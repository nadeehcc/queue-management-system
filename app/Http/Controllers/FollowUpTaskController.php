<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\FollowUpTask;
use App\Models\User;
use Illuminate\Http\Request;

class FollowUpTaskController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:follow-up-task');
    }

    public function index()
    {
        $followUpTasks = FollowUpTask::get();
        return view('followUpTasks.index', compact('followUpTasks'));
    }

    public function viewNewFollowUpTask(Request $request)
    {
        $followUpTasks = FollowUpTask::where('status', 'New')->get();
        return view('followUpTasks.viewNewFollowUpTask', compact('followUpTasks'));
    }

    public function viewInProgressFollowUpTask(Request $request)
    {
        $followUpTasks = FollowUpTask::where('status', 'In Progress')->get();
        return view('followUpTasks.viewInProgressFollowUpTask', compact('followUpTasks'));
    }

    public function viewCompletedFollowUpTask(Request $request)
    {
        $followUpTasks = FollowUpTask::where('status', 'Completed')->get();
        return view('followUpTasks.viewCompletedFollowUpTask', compact('followUpTasks'));
    }

    public function create()
    {
        $appointments = Appointment::get();
        $users = User::get();

        return view('followUpTasks.create', compact('appointments', 'users'));
    }

    public function store(Request $request)
    { 
        $inputValues = $request->all();
        $followUpTask = FollowUpTask::create($inputValues);
    
        return redirect()->route('followUpTasks.show', $followUpTask->id )// show relevant follow up task
                         ->with('success','Follow up task created successfully.');
    }

    public function show(FollowUpTask $followUpTask)
    {
        return view('followUpTasks.show', compact('followUpTask'));
    }

    public function edit(FollowUpTask $followUpTask)
    {
        $users = User::orderBy('name')->pluck('name', 'id')->prepend('None', '');
        return view('followUpTasks.edit', compact('followUpTask', 'users'));
    }

    public function update(Request $request, FollowUpTask $followUpTask)
    { 
        $inputValues = $request->all();
        $followUpTask->update($inputValues);

        return redirect()->route('followUpTasks.index')
                         ->with('success','Follow up task updated successfully.');
    }

    public function destroy(FollowUpTask $followUpTask)
    {
        $followUpTask->delete();
    
        return redirect()->route('followUpTasks.index')
                         ->with('success','Follow up task deleted successfully.');
    }
}
