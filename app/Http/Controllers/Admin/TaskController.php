<?php

namespace App\Http\Controllers\Admin;

use App\Models\Task;
use Illuminate\View\View;
use App\Models\ClientTask;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = Task::where('status',1)->get();
        return view('admin.tasks.index', compact('tasks'));
    }

    public function client_tasks(): View
    {
        return view('admin.tasks.client_tasks');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|boolean',
            'expiration_date' => 'required|date'
        ]);
        Task::create($request->only('name', 'title', 'description', 'status', 'expiration_date','points'));

        return redirect()->route('admin.tasks.index')->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'points' => 'required|integer',
            'status' => 'required|boolean',
            'expiration_date' => 'required|date'
        ]);

        $task->update($request->only('name', 'title', 'description', 'points', 'status', 'expiration_date'));

        return redirect()->route('admin.tasks.index')->with('success', 'Task updated successfully.');
    }


    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('admin.tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function uploadScreenshot(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'screenshot' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $task = Task::findOrFail($request->task_id);
        $path = $request->file('screenshot')->store('screenshots', 'public');
        // dd($request->task_id);
        $clientTask = ClientTask::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'task_id' => $request->task_id,
                'status' => 0,
            ],
            [
                'image_path' => $path,
            ]
        );

        return response()->json([
            'message' => 'Screenshot uploaded!',
            'screenshot_path' => $path,
            'client_task_id' => $clientTask->id
        ]);
    }

    public function approve_reject(Request $request)
    {
        // Validate the request first (optional but recommended)
        $request->validate([
            'client_id' => 'required',  // adjust table name if needed
            'task_id' => 'required', // adjust table name
            'request_status' => 'required', // Assuming 1 = Approve, 2 = Reject
        ]);

        // Extract values
        $clientId = $request->input('client_id');
        $taskId = $request->input('task_id');
        $status = $request->input('request_status');

        // Update the task status
        $task = ClientTask::findOrFail($taskId);

        // Update its status
        $task->status = $status;
        $task->save();

        // Now $task is the updated model instance
        // dd($task);
        // if($task->status==1){
        //     if (($error_code = $this->api->TradeBalance($login, MTEnDealAction::DEAL_BONUS, $amount, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
        //         return redirect()->back()->with('error', MTRetCode::GetError($error_code));
        //     } else {
        //         $deposit_details = BonusTransaction::create([
        //             'email' => $email,
        //             'user_id' => $user->id,
        //             'account_id' => $account->id,
        //             'code' => $code,
        //             'bonus_amount' => $amount,
        //             'bonus_type' => $deposit_type,
        //             'status' => 1,
        //             'admin_remark' => $comment,
        //             'bonus_currency' => $deposit_currency
        //         ]);
        // }

        // Optional: Add a success flash message
        return redirect()->back()->with('success', 'Task status updated successfully.');
    }

}
