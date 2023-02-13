<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [ 'customer_id', 'session_id', 'status', 'uuid', 'token',
                            'scheduledTime', 'arrivedTime', 'estimatedTime', 'servingStartedTime',
                            'servingCompletedTime', 'userId', 'timeSpentInMinutes', 'paid',
    ];

    public function session()
    {
        return $this->belongsTo(Session::Class)->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::Class)->withTrashed();
    }

    public function tasks() 
    {
        return $this->belongsToMany(Task::class)->withTrashed();
    }

    public function followUpTasks() 

    {
        return $this->belongsToMany(FollowUpTask::class)->withTrashed();
    }

    public function estimatedDurationInMinutes() 
    {
        $time = 0;
        foreach ($this->tasks as $task) {
            $time = $time + $task->durationInMinutes;
        }
        return $time;
    }

    public function amount(){
        $amount = 0;
        foreach ($this->tasks as $task) {
            $amount = $amount + $task->amount;
        }
        return $amount;
    }

    public function estimatedCompleteTime() 
    {
        return date("Y-m-d H:i:s", strtotime($this->estimatedTime."+".$this->estimatedDurationInMinutes()."minutes")) ;
    }
}