<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TradeController extends Controller
{
    /**
     * Show the All Trades page.
     */
    public function index()
    {
        return view('admin.trades.index');
    }

    /**
     * Server-side data provider for All Trades DataTable.
     */
    public function getTradesData(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        $query = Trade::with(['account.user'])
            ->select([
                'id',
                'account_id',
                'code',
                'order_id',
                'symbol',
                'type',
                'volume',
                'open_price',
                'close_price',
                'profit',
                'sl',
                'tp',
                'comment',
                'status',
                'state',
                'open_time',
                'close_time',
                'created_at',
                'updated_at',
            ]);

        return DataTables::of($query)
            ->editColumn('id', fn ($row) => (string) $row->id)

            // Global search filter - matches pattern used in other admin controllers
            ->filter(function ($query) use ($request) {
                if (!empty($request->search['value'])) {
                    $searchValue = $request->search['value'];
                    $query->where(function ($q) use ($searchValue) {
                        // Search in trades table columns
                        $q->where('trades.order_id', 'LIKE', "%{$searchValue}%")
                            ->orWhere('trades.code', 'LIKE', "%{$searchValue}%")
                            ->orWhere('trades.symbol', 'LIKE', "%{$searchValue}%")
                            ->orWhere('trades.type', 'LIKE', "%{$searchValue}%")
                            ->orWhere('trades.status', 'LIKE', "%{$searchValue}%")
                            ->orWhereRaw("CAST(trades.volume AS CHAR) LIKE ?", ["%{$searchValue}%"])
                            ->orWhereRaw("CAST(trades.open_price AS CHAR) LIKE ?", ["%{$searchValue}%"])
                            ->orWhereRaw("CAST(trades.close_price AS CHAR) LIKE ?", ["%{$searchValue}%"])
                            ->orWhereRaw("CAST(trades.profit AS CHAR) LIKE ?", ["%{$searchValue}%"])
                            ->orWhereRaw("DATE_FORMAT(trades.open_time, '%Y-%m-%d %H:%i:%s') LIKE ?", ["%{$searchValue}%"])
                            // Search in account code
                            ->orWhereHas('account', function ($accountQuery) use ($searchValue) {
                                $accountQuery->where('code', 'LIKE', "%{$searchValue}%");
                            })
                            // Search in user fullname and email
                            ->orWhereHas('account.user', function ($userQuery) use ($searchValue) {
                                $userQuery->where('fullname', 'LIKE', "%{$searchValue}%")
                                    ->orWhere('email', 'LIKE', "%{$searchValue}%");
                            });
                    });
                }
            })

            ->addColumn('client_name', function ($row) {
                $user = optional(optional($row->account)->user);

                // Prefer fullname field, which is used across CRM
                $full = $user->fullname ?? null;
                $full = $full && strtolower($full) !== 'null' ? trim($full) : '';

                if ($full !== '') {
                    return $full;
                }

                // Fallback: build from firstname / lastname if present
                $first = $user->firstname ?? null;
                $last  = $user->lastname ?? null;

                $first = $first && strtolower($first) !== 'null' ? $first : '';
                $last  = $last && strtolower($last) !== 'null' ? $last : '';

                $name = trim($first . ' ' . $last);

                return $name !== '' ? $name : 'N/A';
            })
            ->addColumn('client_email', function ($row) {
                $email = optional(optional($row->account)->user)->email;

                return $email && strtolower($email) !== 'null' ? $email : 'N/A';
            })
            ->addColumn('account_code', function ($row) {
                $code = optional($row->account)->code;
                $accountId = $row->account_id;

                if (!$code || strtolower($code) === 'null' || !$accountId) {
                    return 'N/A';
                }

                // Use URL helper to match the pattern used in other admin views
                $url = url("/admin/view_account_details/{$accountId}");
                return "<a href=\"{$url}\" class=\"text-primary\" title=\"View Account Details\">{$code}</a>";
            })

            ->addColumn('order_id_display', fn ($row) => $row->order_id ?? $row->code)
            ->addColumn('symbol_display', fn ($row) => "<span class='fw-semibold'>{$row->symbol}</span>")

            ->addColumn('type_display', function ($row) {
                $badge = $row->type === 'buy'
                    ? 'bg-success-transparent text-success'
                    : 'bg-danger-transparent text-danger';

                return "<span class='badge {$badge}'>" . strtoupper($row->type) . '</span>';
            })

            ->addColumn('volume_display', fn ($row) => number_format($row->volume, 2))
            ->addColumn('open_price_display', fn ($row) => number_format($row->open_price, 5))
            ->addColumn('close_price_display', function ($row) {
                return $row->close_price
                    ? number_format($row->close_price, 5)
                    : '<span class="text-muted">-</span>';
            })

            ->addColumn('profit_display', function ($row) {
                $class = $row->profit >= 0 ? 'text-success' : 'text-danger';
                return "<span class='fw-semibold {$class}'>$" . number_format($row->profit, 2) . '</span>';
            })

            ->addColumn('status_display', function ($row) {
                $statusClass = match ($row->status) {
                    'open' => 'bg-primary-transparent text-primary',
                    'closed' => 'bg-success-transparent text-success',
                    'cancelled' => 'bg-danger-transparent text-danger',
                    default => 'bg-secondary-transparent text-secondary',
                };

                return "<span class='badge {$statusClass} rounded-pill'>" . ucfirst($row->status) . '</span>';
            })

            ->addColumn('open_time_display', function ($row) {
                if (! $row->open_time) {
                    return '<span class="text-muted">N/A</span>';
                }

                return "<div class='d-grid'>
                            <div class='date'>{$row->open_time->format('Y-m-d')}</div>
                            <div class='time text-muted'>{$row->open_time->format('H:i:s')}</div>
                        </div>";
            })

            ->addColumn('action', function ($row) {
                $url = route('admin.trades.show', $row->id);
                return "<a href=\"{$url}\" class=\"btn btn-sm btn-light\" title=\"View Trade Details\">
                            <i class=\"fe fe-eye fs-14 text-info\"></i>
                        </a>";
            })

            ->orderColumn('order_id', fn ($q, $order) => $q->reorder()->orderBy('trades.order_id', $order))
            ->orderColumn('symbol', fn ($q, $order) => $q->reorder()->orderBy('trades.symbol', $order))
            ->orderColumn('type', fn ($q, $order) => $q->reorder()->orderBy('trades.type', $order))
            ->orderColumn('volume', fn ($q, $order) => $q->reorder()->orderBy('trades.volume', $order))
            ->orderColumn('open_price', fn ($q, $order) => $q->reorder()->orderBy('trades.open_price', $order))
            ->orderColumn('close_price', fn ($q, $order) => $q->reorder()->orderBy('trades.close_price', $order))
            ->orderColumn('profit', fn ($q, $order) => $q->reorder()->orderBy('trades.profit', $order))
            ->orderColumn('status', fn ($q, $order) => $q->reorder()->orderBy('trades.status', $order))
            ->orderColumn('open_time', fn ($q, $order) => $q->reorder()->orderBy('trades.open_time', $order))

            ->rawColumns([
                'symbol_display',
                'type_display',
                'close_price_display',
                'profit_display',
                'status_display',
                'open_time_display',
                'account_code',
                'action',
            ])
            ->make(true);
    }

    /**
     * Show a single trade details page.
     */
    public function show(Trade $trade)
    {
        $trade->load(['account.user']);

        return view('admin.trades.show', compact('trade'));
    }
}

