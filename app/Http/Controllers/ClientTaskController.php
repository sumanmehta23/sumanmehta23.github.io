<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\ClientTask;
use Illuminate\Http\Request;

class ClientTaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $tasks = Task::where('status',1)->get();

        return view('tasks.index', compact('tasks','user'));
    }

    public function client_verify(Request $request)
    {

        $request->validate([
            'task_id' => 'required',
        ]);

        $clientTask = ClientTask::where('user_id',auth()->id())->where('id',$request->task_id)->first();
        $clientTask->client_verification = 1;
        $clientTask->save();

        return response()->json([
            'message' => 'Task submitted successfully!',
        ]);

    }
}
