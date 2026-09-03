<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's default users for each role.
     */
    public function run(): void
    {
        $users = [
            [
                'role' => 'Administrador',
                'name' => 'Samuel Supervisor',
                'email' => 'admin@gmail.com',
                'username' => 'samuel.admin',
                'password' => 'admin1234',
            ],
            [
                'role' => 'Vendedor',
                'name' => 'Valeria Ventas',
                'email' => 'ventas@gmail.com',
                'username' => 'valeria.ventas',
                'password' => 'vendedor1234',
            ],
            [
                'role' => 'Comprador',
                'name' => 'Camila Cliente',
                'email' => 'comprador@gmail.com',
                'username' => 'camila.cliente',
                'password' => 'comprador1234',
            ],
            [
                'role' => 'Almacén',
                'name' => 'Armando Almacén',
                'email' => 'almacen@gmail.com',
                'username' => 'armando.almacen',
                'password' => 'almacen1234',
            ],
        ];

        foreach ($users as $data) {
            $role = Role::where('name', $data['role'])->first();

            if (! $role) {
                continue;
            }

            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'password' => Hash::make($data['password']),
                    'role_id' => $role->id,
                ]
            );
        }
    }
}
