<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'              => 'Test User',
            'email'             => 'user@gmail.com',
            'email_verified_at' => Carbon::now(),
            'password'          => Hash::make('user@123'),
            'role'              => 'user',
            'is_admin'         => false,
            // Audit
            'created_by'        => 1,
        ]);
    }
}