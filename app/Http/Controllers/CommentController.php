<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function index()
    {
        //
    }

    public function create(Request $request)
    {
       //
    }

    public function store(Request $request)
    {
        $inputValues = $request->all();
        Comment::create($inputValues);
        $follow_up_task_id = $request->input('follow_up_task_id');
        return redirect()->route('followUpTasks.show',  $follow_up_task_id)
                        ->with('success','Comment created successfully.');
    }

    public function show(Comment $comment)
    {
        //
    }

    public function edit(Comment $comment)
    {
        //
    }

    public function update(Request $request, Comment $comment)
    {
        //
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        $follow_up_task_id = $comment->follow_up_task_id;
        return redirect()->route('followUpTasks.show',  $follow_up_task_id)
                         ->with('success','Comment deleted successfully.');
    }
}
