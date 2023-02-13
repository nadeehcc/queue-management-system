<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [ 'follow_up_task_id', 'comment', 'user_id' ];

    public function user()
    {
        return $this->belongsTo(User::Class)->withTrashed();
    }
}
