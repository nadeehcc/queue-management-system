<?php

namespace Database\Seeders;

use App\Models\Location;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run()
    {
        Location::create(['name' => 'Room 101']);
        Location::create(['name' => 'Room 102']);
        Location::create(['name' => 'Room 103']);
        Location::create(['name' => 'Room 104']);
        Location::create(['name' => 'Room 105']);
        Location::create(['name' => 'Room 201']);
        Location::create(['name' => 'Room 202']);
        Location::create(['name' => 'Room 203']);
    }
}
