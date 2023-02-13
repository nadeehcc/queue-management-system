<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:configurations');
    }
    
    public function index()
    {
        $tasks = Task::get();
        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('tasks.create');
    }

    public function store(Request $request)
    {    
        $request->validate([
            'name' => 'unique:tasks',
        ]);  
        
        $inputValues = $request->all();
        Task::create($inputValues);

        return redirect()->route('tasks.index')
                        ->with('success','Tasks created successfully.');
    }

    public function show(Task $task)
    {
    }

    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'name' => 'unique:tasks',
        ]); 
        
        $inputValues = $request->all();
        $task->update($inputValues);
    
        return redirect()->route('tasks.index')
                        ->with('success','Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
    
        return redirect()->route('tasks.index')
                        ->with('success','Task deleted successfully.');
    }
}
