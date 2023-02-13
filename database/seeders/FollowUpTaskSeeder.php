<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FollowUpTask;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FollowUpTaskSeeder extends Seeder
{
    public function run()
    {
        FollowUpTask::create([
            'appointment_id' => 1, 
            'user_id' => 3, 
            'summary' => 'Arrange endoscopy', 
            'description' => 'Arrange endoscopy within 12 hours. Send the report directly to doctor, Priority High.', 
            'status' => 'In progress', 
            'user_id' => 3]);

        FollowUpTask::create([
            'appointment_id' => 2, 
            'user_id' => 2, 
            'summary' => 'Arrange CT scan', 
            'description' => 'Arrange CT scan within this week. Send the report directly to doctor, Priority High.', 
            'status' => 'Completed']);

        FollowUpTask::create([
            'appointment_id' => 4, 
            'summary' => 'Arrange ultrasound scan', 
            'description' => 'Arrange ultrasound scan immediately. Send the report directly to doctor, Priority High.', 
            'status' => 'New', 
            'user_id' => 6]);
    }
}