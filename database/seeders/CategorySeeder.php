<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Leches fluidas', 'description' => 'Leches UHT, light, descremadas y deslactosadas para consumo diario.'],
            ['name' => 'Leches saborizadas', 'description' => 'Leches chocolatadas y saborizadas para consumo individual o familiar.'],
            ['name' => 'Leches en polvo', 'description' => 'Leches y alimentos lacteos en polvo de alta rotacion institucional y familiar.'],
            ['name' => 'Yogurt', 'description' => 'Yogurts bebibles, griegos, light, deslactosados y escolares.'],
            ['name' => 'Bebidas lacteas', 'description' => 'Pilfrut y bebidas lacteas de alta salida en tiendas y supermercados.'],
            ['name' => 'Jugos y nectares', 'description' => 'Juguitos, nectares y bebidas frutales para canal minorista y familiar.'],
            ['name' => 'Alimento de soya', 'description' => 'Bebidas de soya en distintas variedades y sabores.'],
            ['name' => 'Agua', 'description' => 'Agua sin gas, con gas y bidones para hogares y negocios.'],
            ['name' => 'Te helado', 'description' => 'Bebidas listas para tomar en formato individual.'],
            ['name' => 'Reposteria', 'description' => 'Cremas, leches evaporadas y condensadas para cocina y panaderia.'],
            ['name' => 'Dulce de leche', 'description' => 'Dulce de leche en formatos retail y food service.'],
            ['name' => 'Mantequillas y margarinas', 'description' => 'Mantequillas y margarinas de uso domestico y comercial.'],
            ['name' => 'Mermeladas', 'description' => 'Mermeladas listas para desayuno y reposteria.'],
            ['name' => 'Postres', 'description' => 'Gelatinas, flanes y postres de alta rotacion.'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
