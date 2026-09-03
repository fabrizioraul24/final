<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     */
    public function run(): void
    {
        $timestamp = now();

        $roles = [
            ['name' => 'Administrador', 'description' => 'Control total del ecosistema Pil Andina'],
            ['name' => 'Vendedor', 'description' => 'Gestión comercial y seguimiento de pedidos'],
            ['name' => 'Comprador', 'description' => 'Clientes finales con acceso al catálogo y compras'],
            ['name' => 'Almacén', 'description' => 'Control de inventario, lotes y despachos'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                [
                    'description' => $role['description'],
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
                ]
            );
        }
    }
}
