<?php
  
namespace Database\Seeders;
  
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
  
class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
           'configurations',  
           'customer', 
           'session-list', 
           'session-create', 
           'session-edit', 
           'session-delete',
           'recalculate',
           'appointment-list',
           'appointment-create', 
           'appointment-change-status',
           'appointment-payment', 
           'follow-up-task',
           'appointment-arrive',
           'appointment-cancel',
           'reports', 
           'check-in-code',
        ];
      
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}