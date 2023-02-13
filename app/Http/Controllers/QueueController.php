<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Task;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:configurations');
    }

    public function index()
    {
        $queues = Queue::get();
        return view('queues.index', compact('queues'));
    }

    public function create()
    {
        return view('queues.create');
    }

    public function store(Request $request)
    {  
        $request->validate([
            'name' => 'unique:queues',
        ]); 

        $inputValues = $request->all();
        Queue::create($inputValues);
       
        return redirect()->route('queues.index')
                        ->with('success','Queue created successfully.');
    }

    public function show(Queue $queue)
    {
    }

    public function edit(Queue $queue)
    {
        return view('queues.edit', compact('queue'));
    }

    public function update(Request $request, Queue $queue)
    {   
        $request->validate([
            'name' => 'unique:queues,name,'.$queue->id,
        ]);

        $inputValues = $request->all();
        $queue->update($inputValues);
        
        return redirect()->route('queues.index')
                        ->with('success','Queue updated successfully');
    }

    public function destroy(Queue $queue)
    {
        $queue->delete();
    
        return redirect()->route('queues.index')
                        ->with('success','Queue deleted successfully');
    }
}