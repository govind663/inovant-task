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
            'email'             => 'test@example.com',
            'email_verified_at' => Carbon::now(),
            'password'          => Hash::make('password'),

            // Audit
            'created_by'        => 1,
        ]);
    }
}