<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'La Paz', 'code' => 'LPZ', 'department' => 'La Paz'],
            ['name' => 'El Alto', 'code' => 'EA', 'department' => 'La Paz'],
            ['name' => 'Santa Cruz', 'code' => 'SCZ', 'department' => 'Santa Cruz'],
            ['name' => 'Cochabamba', 'code' => 'CBA', 'department' => 'Cochabamba'],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(['code' => $city['code']], $city);
        }
    }
}
