<?php

namespace Database\Seeders;

use App\Models\CheckInCode;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CheckInCodeSeeder extends Seeder
{
    public function run()
    {
        CheckInCode::create([ 'code' => rand(10000, 99999)]);
    }
}