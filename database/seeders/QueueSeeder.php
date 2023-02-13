<?php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\Task;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Queue::create([ 'name' => 'Dr. Aruni Abesingha', 'type' => 'Psychiatrist']);
        Queue::create([ 'name' => 'Dr. Dulani Kottachi', 'type' => 'Endocrinologist']);
        Queue::create([ 'name' => 'Dr. Gayani Premawansa', 'type' => 'Gynaecologist']);
    }
}
