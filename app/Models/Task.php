<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [ 'name', 'durationInMinutes', 'amount' ];

    public function getComboboxTextAttribute()//pad '-' to name to make the length 25 + pad'-'to amount to left to make the length 10
    {
        return str_pad($this->name, 25, '_') . str_pad('Rs. ' . number_format($this->amount, 2), 10, '_', STR_PAD_LEFT).
               str_pad($this->durationInMinutes, 10, '_', STR_PAD_LEFT) . 'min';
    }
}
 