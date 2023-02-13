<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;

class CommentSeeder extends Seeder
{
    public function run()
    {
        Comment::create(['follow_up_task_id' => 1, 
                         'user_id'=> 2,
                         'comment' => 'Scheduled 8.30pm.']);
        
        Comment::create(['follow_up_task_id' => 1, 
                         'user_id'=> 3,
                         'comment' => 'Started to Prepare patient for the scan.']);
        
        Comment::create(['follow_up_task_id' => 2,
                         'user_id'=> 4,
                         'comment' => 'Scheduled 3.30pm.']);
        
        Comment::create(['follow_up_task_id' => 3, 
                         'user_id'=> 4,
                         'comment' => 'Report will be ready by tomorrow evening.']);
    }
}