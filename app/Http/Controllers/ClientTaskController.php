<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class ClientTaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $tasks = Task::where('status',1)->get();

        return view('tasks.index', compact('tasks','user'));
    }
}
