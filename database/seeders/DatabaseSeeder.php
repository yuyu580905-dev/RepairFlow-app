<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            [
                'name' => 'テスト依頼者',
                'email' => 'requester@example.com',
                'role' => 'requester',
            ],
            [
                'name' => 'テスト技術者',
                'email' => 'technician@example.com',
                'role' => 'technician',
            ],
            [
                'name' => 'テスト管理者',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'is_active' => true,
                    'password' => $password,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
