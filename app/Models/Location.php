<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [ 'name' ];


    public function sessions() 
    {
        return $this->hasMany(Session::class);
    }

    public function upComingSessions() 
    {
        $today = date('Y-m-d');
        return Session::where('location_id', $this->id)->where('date', '>=', $today)->get();
    }
}
