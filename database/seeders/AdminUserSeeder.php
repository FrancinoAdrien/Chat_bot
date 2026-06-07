<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['matricule' => 'ADMIN001'],
            [
                'name' => 'Admin',
                'prenom' => 'Admin',
                'poste' => 'Administrateur',
                'role' => 'admin',
                'password' => Hash::make('P455w0rd!'),
                'is_active' => true,
            ]
        );
    }
}
