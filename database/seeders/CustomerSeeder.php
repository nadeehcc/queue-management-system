<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    public function run()
    {       
        $faker = Faker::create();
        for ($x = 0; $x <= 100; $x++) {
            Customer::create(['name' => $faker->name(), 'phoneNumber' => '07'. rand(20000000, 99999999)]);
        }   
    }
}