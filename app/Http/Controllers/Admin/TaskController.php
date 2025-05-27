<?php

namespace App\Http\Controllers\Admin;

use App\Models\Task;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use Illuminate\View\View;
use App\Models\ClientTask;
use App\MT5\MTEnDealAction;
use App\Services\MT5Service;
use Illuminate\Http\Request;
use App\Services\MailService;
use App\Helpers\AccountHelper;
use App\Models\BonusTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $api;
    protected $settings;
    protected $mailService;

    public function __construct(MTWebAPI $api, MailService $mailService, MT5Service $mt5Service)
    {
        $this->settings = settings();
        $this->api = $api;
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $email = session('clogin');
        AccountHelper::updateLiveAndDemoAccounts($email, $api);
    }

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

        $validator = Validator::make($request->all(), [
            'screenshot' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
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
        $clientTask = ClientTask::where('user_id', auth()->id())
            ->where('task_id', $task->id)
            ->first();
        if ($clientTask) {
            $clientTask->image_path = $path;
            $clientTask->status = 0; // Optionally reset status
            $clientTask->save();
        } else {
            $clientTask = ClientTask::create([
                'user_id' => auth()->id(),
                'task_id' => $task->id,
                'image_path' => $path,
                'status' => 0, // Assuming you want to default to status 0
            ]);
        }

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
        $task = ClientTask::with('task')->findOrFail($taskId);



        $account = Account::where('user_id', $clientId)->whereHas('accountType', function ($query) {
            $query->where('ac_group', '!=', 'LM\\B-Book\\10x\\DF-B');
        })->first();

        $settings = settings();
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
        $comment = "Task Bonus";

        // Update its status

        $task->status = $status;
        $task->save();

        // Now $task is the updated model instance
        // dd($task);
        if($task->status==1){
            if (($error_code = $this->api->TradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $task->task->points, $comment, $ticket, true)) !== MTRetCode::MT_RET_OK) {
                return redirect()->back()->with('error', MTRetCode::GetError($error_code));
            } else {
                $deposit_details = BonusTransaction::create([
                    'email' => $account->email,
                    'user_id' => $clientId,
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'bonus_amount' => $task->task->points,
                    'bonus_type' => $comment,
                    'status' => 1,
                    'admin_remark' => $comment,
                    'bonus_currency' => 'USD'
                ]);
            }
        }
        // Optional: Add a success flash message
        return redirect()->back()->with('success', 'Task status updated successfully.');
    }
}
