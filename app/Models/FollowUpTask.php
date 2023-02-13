<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUpTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [ 'appointment_id', 'summary', 'description', 'status', 'user_id' ];

    public function comments() 
    {
        return $this->hasMany(Comment::class);
    }

    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    public function appointment() 
    {
        return $this->belongsTo(Appointment::class);
    }
}
