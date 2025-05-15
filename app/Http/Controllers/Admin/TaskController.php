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


}
