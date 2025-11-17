<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Imports\AffiliatesImport;
use App\Exports\AffiliatesExport;
use App\Exports\AffiliatesSampleExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AffiliateController extends Controller
{
    /**
     * Display a listing of the affiliates.
     */
    public function index()
    {
        return view('admin.affiliates.index');
    }

    /**
     * Get affiliates data for DataTable
     */
    public function getAffiliates(Request $request)
    {
        try {
            $query = Affiliate::query();

            // Search functionality
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->where(function ($q) use ($search) {
                    $q->where('affiliate_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            $totalRecords = Affiliate::count();
            $filteredRecords = $query->count();

            // Ordering
            if ($request->has('order')) {
                $orderColumn = $request->columns[$request->order[0]['column']]['data'];
                $orderDir = $request->order[0]['dir'];
                $query->orderBy($orderColumn, $orderDir);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            $affiliates = $query->skip($start)->take($length)->get();

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $affiliates
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching affiliates: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch affiliates data'
            ], 500);
        }
    }

    /**
     * Show the import form
     */
    public function importForm()
    {
        return view('admin.affiliates.import');
    }

    /**
     * Import affiliates from Excel file
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $import = new AffiliatesImport();
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            $importedCount = $import->getImportedCount();
            $updatedCount = $import->getUpdatedCount();
            $skippedCount = $import->getSkippedCount();

            DB::commit();

            $response = [
                'success' => true,
                'message' => "Import completed successfully!",
                'data' => [
                    'imported' => $importedCount,
                    'updated' => $updatedCount,
                    'skipped' => $skippedCount,
                    'total' => $importedCount + $updatedCount,
                ]
            ];

            if (!empty($failures)) {
                $errorMessages = [];
                foreach ($failures as $failure) {
                    $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
                }
                $response['warnings'] = $errorMessages;
                $response['message'] = "Import completed with some errors.";
            }

            return response()->json($response);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation errors in Excel file',
                'errors' => $errorMessages
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Affiliate import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export affiliates to Excel
     */
    public function export()
    {
        try {
            return Excel::download(new AffiliatesExport(), 'affiliates_' . date('Y-m-d_His') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Affiliate export error: ' . $e->getMessage());
            return back()->with('error', 'Failed to export affiliates');
        }
    }

    /**
     * Download sample Excel template
     */
    public function downloadSample()
    {
        try {
            return Excel::download(new AffiliatesSampleExport(), 'affiliates_sample_template.xlsx');
        } catch (\Exception $e) {
            Log::error('Sample download error: ' . $e->getMessage());
            return back()->with('error', 'Failed to download sample template');
        }
    }

    /**
     * Show affiliate details
     */
    public function show($id)
    {
        try {
            $affiliate = Affiliate::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $affiliate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Affiliate not found'
            ], 404);
        }
    }

    /**
     * Update affiliate status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,pending',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status value'
            ], 422);
        }

        try {
            $affiliate = Affiliate::findOrFail($id);
            $affiliate->status = $request->status;
            $affiliate->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Status update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Delete affiliate
     */
    public function destroy($id)
    {
        try {
            $affiliate = Affiliate::findOrFail($id);
            $affiliate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Affiliate deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete affiliate'
            ], 500);
        }
    }
}
