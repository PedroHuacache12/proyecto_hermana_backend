<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@catalogo.com'],
            [
                'name'            => 'Super Admin',
                'password'        => Hash::make('Admin1234!'),
                'whatsapp_number' => null,
                'role'            => 'superadmin',
            ]
        );
    }
}
