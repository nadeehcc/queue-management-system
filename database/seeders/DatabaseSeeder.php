<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            PermissionTableSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            CheckInCodeSeeder::class,
            TaskSeeder::class,
            QueueSeeder::class,
            LocationSeeder::class,
            SessionSeeder::class,
            CustomerSeeder::class,
            AppointmentSeeder::class,
            FollowUpTaskSeeder::class,
            CommentSeeder::class
        ]);
    }
}
