<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Acme\StoreBundle\Repository\DateTime;

class Session extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['queue_id', 'location_id', 'date', 'startTime', 'endTime' ];

    public function location()
    {
        return $this->belongsTo(Location::Class)->withTrashed();
    }

    public function queue()
    {
        return $this->belongsTo(Queue::Class)->withTrashed();
    }

    public function appointments() 
    {
        return $this->hasMany(Appointment::class)->withTrashed();
    }

    public function getNextToken() 
    {
        $lastAppointment = Appointment::where('session_id', $this->id)->orderBy('token','desc')->first();
        if($lastAppointment != null)
        {
            return $lastAppointment->token + 1;
        }
        else
        {
            return 1;
        }
    }

    public function getNextAvailableTime() 
    {
        $lastAppointment = Appointment::where('session_id', $this->id)->
                                        whereNotIn('status', ['Completed', 'Cancel Requested', 'Canceled'])->
                                        orderBy('token','desc')->first();
        if($lastAppointment != null)
        {
            return $lastAppointment->estimatedCompleteTime();
        }
        else
        {
            return date("Y-m-d H:i:s", strtotime($this->date.' '.$this->startTime));
        }
    }

    public function getAvailableRemainingTime()
    {
        $lastAppointment = Appointment::where('session_id', $this->id)->
                                         whereNotIn('status', ['Cancel Requested', 'Canceled'])->
                                         orderBy('token','desc')->first();
        
        if( $lastAppointment == null)
        {
           $from = date_create($this->startTime);
           $to = date_create($this->endTime);
           return date_diff($from, $to)->format("%r%hh %imins");
        }
        else
        {
            $from = date_create($lastAppointment->estimatedCompleteTime());
            $to = date_create($this->date.' '.$this->endTime);
            return date_diff($from, $to)->format("%r%hh %imins");
        }
    }

    public function getAvailableRemainingTimeInMinutes()
    {
        $lastAppointment = Appointment::where('session_id', $this->id)->
                                         whereNotIn('status', ['Cancel Requested', 'Canceled'])->
                                         orderBy('token','desc')->first();
        
        if( $lastAppointment == null)
        {
            $to = strtotime($this->endTime);
            $from = strtotime($this->startTime);
           return ($to - $from)/60;
        }
        else
        {
            $to = strtotime($this->date.' '.$this->endTime);
            $from = strtotime($lastAppointment->estimatedCompleteTime());
            return ($to - $from)/60;
        }
    }

    public function started() 
    {
        $appointment = Appointment::where('session_id', $this->id)->
                           whereIn('status', ['Completed', 'Serving', 'Waiting', 'Invited'])->first();
        return $appointment != null;
    }

    public function completed() 
    {
        $appointment = Appointment::where('session_id', $this->id)->
                           whereIn('status', ['Serving', 'Waiting', 'Invited', 'Arrived', 'Scheduled'])->first();
        return $appointment == null;
    }

    public function status() 
    {
        if($this->completed()){
            return 'Completed';
        }
        else if($this->started()){
            return 'Started';
        }
        else {
            return 'Not Started';
        }
    }

    public function pendingCount()
    {
        $appointments = Appointment::where('session_id', $this->id)->
                           whereIn('status', ['Serving', 'Waiting', 'Invited', 'Arrived', 'Scheduled'])->get();
        return count($appointments);
    }

    public function completedCount()
    {
        $appointments = Appointment::where('session_id', $this->id)->
                           where('status', 'Completed')->get();
        return count($appointments);
    }

    public function canceledCount()
    {
        $appointments = Appointment::where('session_id', $this->id)->
                           whereIn('status', ['Canceled','Cancel Requested'])->get();
        return count($appointments);
    }

    public function completedAmount()
    {
        $appointments = Appointment::where('session_id', $this->id)->
                           where('status', 'Completed')->get();

        $totalAmount = 0;
        foreach($appointments as $appointment)
        {
            $totalAmount = $totalAmount + $appointment->amount();
        }

        return $totalAmount;
    }
}