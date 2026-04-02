<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@manychamps.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_superadmin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Crear equipo personal si no tiene uno (como hace Jetstream al registrar)
        if ($user->ownedTeams()->count() === 0) {
            $user->ownedTeams()->save(Team::forceCreate([
                'user_id' => $user->id,
                'name' => 'Admin',
                'personal_team' => true,
            ]));
        }
    }
}
