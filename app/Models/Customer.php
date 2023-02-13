<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [ 'name', 'phoneNumber' ];
    
    public function appointments() 
    {
        return $this->hasMany(Appointment::class);
    }

    public function followUpTasks() 
    {
        return $this->hasMany(FollowUpTask::class);
    }
}