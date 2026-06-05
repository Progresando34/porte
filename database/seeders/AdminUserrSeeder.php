<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminUserrSeeder extends Seeder
{
    public function run()
    {
        // Buscar el perfil de administrador
        $adminProfile = Profile::where('name', 'admin')->first();
        
        if (!$adminProfile) {
            // Si no existe el perfil 'admin', buscar por ID o crear uno
            $adminProfile = Profile::firstOrCreate(
                ['name' => 'admin'],
                ['description' => 'Administrador del sistema']
            );
            $this->command->info('✅ Perfil "admin" creado');
        }
        
        // Verificar si el usuario ya existe
        $existingUser = User::where('email', 'adminlocal@admin.com')->first();
        
        if ($existingUser) {
            $this->command->warn('⚠️ El usuario adminlocal@admin.com ya existe');
            
            // Opcional: Actualizar contraseña
            $existingUser->update([
                'password' => Hash::make('password123'), // Cambia por la contraseña que quieras
                'profile_id' => $adminProfile->id,
            ]);
            $this->command->info('✅ Usuario actualizado correctamente');
        } else {
            // Crear nuevo usuario administrador
            $user = User::create([
                'name' => 'Administrador Local',
                'email' => 'adminlocal@admin.com',
                'password' => Hash::make('password123'), // Cambia por una contraseña segura
                'profile_id' => $adminProfile->id,
                'avatar' => null,
            ]);
            
            $this->command->info('✅ Usuario administrador creado exitosamente');
            $this->command->info('📧 Email: adminlocal@admin.com');
            $this->command->info('🔑 Contraseña: password123');
        }
        
        // Mostrar información del usuario
        $user = User::where('email', 'adminlocal@admin.com')->first();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📋 DATOS DEL ADMINISTRADOR:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('ID: ' . $user->id);
        $this->command->info('Nombre: ' . $user->name);
        $this->command->info('Email: ' . $user->email);
        $this->command->info('Perfil: ' . ($user->profile->name ?? 'No asignado'));
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}