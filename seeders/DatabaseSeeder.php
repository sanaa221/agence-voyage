<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {

        User::create([
            'name' => 'Directeur Agence',
            'email' => 'admin@agence.ma',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

      
        User::create([
            'name' => 'Ahmed Client',
            'email' => 'client@test.ma',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);


        $this->call([
            TripSeeder::class,
        ]);
    }
}