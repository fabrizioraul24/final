<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Models\City;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Centro Logístico Santa Cruz',
                'code' => 'SCZ',
                'city' => 'Santa Cruz',
                'address' => 'Av. Cristo Redentor Km 9',
            ],
            [
                'name' => 'Planta Cochabamba',
                'code' => 'CBA',
                'city' => 'Cochabamba',
                'address' => 'Av. Blanco Galindo Km 5',
            ],
            [
                'name' => 'Depósito La Paz',
                'code' => 'LPZ',
                'city' => 'La Paz',
                'address' => 'Zona Achocalla S/N',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            $cityId = City::where('name', $warehouse['city'])->value('id');

            Warehouse::updateOrCreate(
                ['code' => $warehouse['code']],
                [
                    'name' => $warehouse['name'],
                    'city' => $warehouse['city'],
                    'city_id' => $cityId,
                    'address' => $warehouse['address'],
                    'capacity_min' => 0,
                    'capacity_max' => null,
                ]
            );
        }
    }
}
