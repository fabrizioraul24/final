<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = User::where('email', 'admin@gmail.com')->value('id')
            ?? User::whereHas('role', fn ($query) => $query->where('name', 'Administrador'))->value('id')
            ?? User::query()->value('id')
            ?? 1;

        Company::withTrashed()
            ->where('company_type', 'empresa_institucional')
            ->forceDelete();

        foreach ($this->institutionalCompanies() as $index => $data) {
            $this->upsertCompany(array_merge($data, [
                'company_type' => 'empresa_institucional',
                'nit' => sprintf('920%06d', $index + 1),
                'created_by' => $creatorId,
            ]));
        }

        foreach ($this->neighborhoodStores() as $index => $data) {
            $this->upsertCompany(array_merge($data, [
                'company_type' => 'tienda_barrio',
                'nit' => sprintf('930%06d', $index + 1),
                'created_by' => $creatorId,
            ]));
        }
    }

    private function upsertCompany(array $data): void
    {
        $cityId = City::where('name', $data['city'])->value('id');
        $emailSlug = Str::slug($data['name'], '.');
        $addressQuery = rawurlencode($data['address'].', '.$data['city'].', Bolivia');

        Company::withTrashed()->updateOrCreate(
            ['nit' => $data['nit']],
            [
                'company_type' => $data['company_type'],
                'name' => $data['name'],
                'email' => $data['email'] ?? $emailSlug.'@clientes.pil.bo',
                'phone' => $data['phone'] ?? (string) random_int(22000000, 28999999),
                'address' => $data['address'],
                'city' => $data['city'],
                'city_id' => $cityId,
                'owner_first_name' => $data['owner_first_name'],
                'owner_last_name_paterno' => $data['owner_last_name_paterno'],
                'owner_last_name_materno' => $data['owner_last_name_materno'],
                'created_by' => $data['created_by'],
                'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query='.$addressQuery,
                'deleted_at' => null,
            ]
        );
    }

    private function institutionalCompanies(): array
    {
        $rows = [
            ['Hipermaxi', 'Supermercado cadena', 'Muy alto'],
            ['Fidalga', 'Supermercado cadena', 'Muy alto'],
            ['Ketal', 'Supermercado premium', 'Muy alto'],
            ['IC Norte', 'Supermercado', 'Muy alto'],
            ['Mercado Lider', 'Retail alimentos', 'Alto'],
            ['Supermercados Andy', 'Supermercado', 'Alto'],
            ['Brosso', 'Heladeria/Pasteleria/Cafeteria', 'Muy alto'],
            ['Pasteleria Michelline', 'Pasteleria industrial', 'Muy alto'],
            ['The Cake', 'Pasteleria premium', 'Alto'],
            ['Pigalle Reposteria', 'Pasteleria', 'Alto'],
            ['Coconut Bakery', 'Panaderia/Pasteleria', 'Alto'],
            ['Chez Tim', 'Panaderia/Pasteleria', 'Alto'],
            ['3600 Pasteleria de Altura', 'Pasteleria', 'Alto'],
            ['Unuy Pasteleria', 'Pasteleria', 'Alto'],
            ['Lecker Brot', 'Panaderia', 'Alto'],
            ['Casa Goytia', 'Pasteleria/Cafe', 'Alto'],
            ['Alexander Coffee', 'Cafeteria cadena', 'Alto'],
            ['The Writers Coffee', 'Cafeteria', 'Medio-Alto'],
            ['Typica Cafe', 'Cafeteria', 'Medio-Alto'],
            ['Roaster Boutique', 'Cafeteria', 'Medio-Alto'],
            ['Cafe del Mundo', 'Cafeteria', 'Medio'],
            ['Cafe Banais', 'Cafeteria', 'Medio'],
            ['Cafe Epico', 'Cafeteria', 'Medio'],
            ['Cafe con Pan', 'Cafeteria/Panaderia', 'Medio'],
            ['Diletto', 'Cafeteria/Pasteleria', 'Alto'],
            ["Eli's Confiteria", 'Confiteria', 'Alto'],
            ['Avellana Pasteleria', 'Pasteleria', 'Medio-Alto'],
            ['Praline Reposteria', 'Pasteleria', 'Medio-Alto'],
            ['Panaderia Copacabana', 'Panaderia', 'Alto'],
            ['Popular Cocina Boliviana', 'Restaurante', 'Medio-Alto'],
            ['Pollos Copacabana', 'Cadena gastronomica', 'Muy alto'],
            ['Burger King Bolivia La Paz', 'Comida rapida', 'Alto'],
            ['Subway Bolivia La Paz', 'Comida rapida', 'Medio'],
            ['Gustu', 'Restaurante premium', 'Alto'],
            ['La Mar Bolivia', 'Restaurante', 'Medio-Alto'],
            ['Mestizo Restaurante', 'Restaurante', 'Medio'],
            ['Manq-a', 'Gastronomia', 'Medio-Alto'],
            ['Restaurante La Tranquera', 'Restaurante', 'Alto'],
            ['Casa Nostra', 'Restaurante', 'Medio'],
            ['El Vagon del Sur', 'Restaurante', 'Medio'],
            ['Ritz Apart Hotel', 'Hotel 5 estrellas', 'Muy alto'],
            ['Atix Hotel', 'Hotel premium', 'Alto'],
            ['Camino Real Aparthotel', 'Hotel', 'Alto'],
            ['Hotel Europa', 'Hotel', 'Alto'],
            ['Stannum Boutique Hotel', 'Hotel', 'Alto'],
            ['Hotel Presidente', 'Hotel', 'Alto'],
            ['Hotel Gloria La Paz', 'Hotel', 'Medio-Alto'],
            ['Hotel Presidente Suites', 'Hotel', 'Medio-Alto'],
            ['Catering Eventos La Paz', 'Catering', 'Muy alto'],
            ['Catering Corporativo La Paz', 'Catering', 'Muy alto'],
            ['Delizia', 'Industria alimentos/helados', 'Muy alto'],
            ['Flor de Leche', 'Industria lactea', 'Muy alto'],
            ['SOALPRO', 'Industria alimentos', 'Muy alto'],
            ['INPASTAS', 'Industria alimentos', 'Alto'],
            ['Stege', 'Industria alimentos', 'Alto'],
            ['Industrias Copacabana', 'Industria alimentos', 'Muy alto'],
            ['Irupana', 'Industria alimentos', 'Medio-Alto'],
            ['El Ceibo', 'Alimentos/chocolates', 'Medio'],
            ["Mabel's", 'Alimentos', 'Alto'],
            ['La Francesa', 'Panificacion', 'Alto'],
            ['Alicorp Bolivia', 'Alimentos', 'Alto'],
            ['Industrias Venado', 'Alimentos', 'Alto'],
            ['Embol Bolivia', 'Bebidas/distribucion', 'Medio'],
            ['Cerveceria Boliviana Nacional', 'Industria', 'Medio'],
            ['Distribuidora Venado', 'Distribucion', 'Alto'],
            ['Distribuidora Hansa', 'Distribucion', 'Alto'],
            ['Distribuidora Disbol', 'Distribucion', 'Alto'],
            ['Distribuidora PIL La Paz', 'Distribucion', 'Muy alto'],
            ['Mayoristas de alimentos La Paz', 'Distribucion', 'Alto'],
            ['Makro Distribucion', 'Mayorista', 'Alto'],
            ['Universidad Catolica Boliviana', 'Institucional comedor', 'Alto'],
            ['Universidad Mayor de San Andres', 'Institucional comedor', 'Alto'],
            ['Universidad Privada Boliviana La Paz', 'Institucional', 'Medio-Alto'],
            ['Unifranz La Paz', 'Institucional', 'Medio-Alto'],
            ['Universidad del Valle La Paz', 'Institucional', 'Medio-Alto'],
            ['Clinica CIES', 'Clinica/comedor', 'Medio-Alto'],
            ['Clinica del Sur', 'Clinica/comedor', 'Medio-Alto'],
            ['Caja Nacional de Salud La Paz', 'Institucional', 'Muy alto'],
            ['Hospitales privados con comedor', 'Institucional', 'Alto'],
            ['Empresas mineras con oficinas en La Paz', 'Institucional', 'Alto'],
            ['YPFB La Paz', 'Institucional', 'Alto'],
            ['Banco Nacional de Bolivia', 'Institucional', 'Medio'],
            ['Banco Mercantil Santa Cruz', 'Institucional', 'Medio'],
            ['Banco BISA', 'Institucional', 'Medio'],
            ['Banco de Credito BCP', 'Institucional', 'Medio'],
            ['Drogueria INTI', 'Institucional', 'Medio'],
            ['Laboratorios Bago Bolivia', 'Institucional', 'Medio'],
            ['Laboratorios Vita', 'Institucional', 'Medio'],
            ['Laboratorios COFAR', 'Institucional', 'Medio'],
            ['Importadoras de alimentos La Paz', 'Distribucion', 'Alto'],
            ['Salones de te tradicionales La Paz', 'Pasteleria', 'Medio-Alto'],
            ['Panaderias industriales El Alto', 'Produccion', 'Alto'],
            ['Fabricas de postres congelados', 'Produccion', 'Muy alto'],
            ['Productores de helados artesanales grandes', 'Produccion', 'Alto'],
            ['Empresas de eventos sociales La Paz', 'Catering', 'Alto'],
            ['Empresas de alimentacion empresarial', 'Catering', 'Muy alto'],
            ['Comedores industriales', 'Institucional', 'Muy alto'],
            ['Empresas de catering minero', 'Catering', 'Muy alto'],
            ['Distribuidores mayoristas El Alto', 'Distribucion', 'Alto'],
            ['Centros gastronomicos con produccion propia', 'Gastronomia', 'Alto'],
        ];

        $owners = $this->owners();
        $addresses = $this->institutionalAddresses();

        return array_map(function (array $row, int $index) use ($owners, $addresses) {
            $owner = $owners[$index % count($owners)];
            $city = $index >= 91 || str_contains($row[0], 'El Alto') ? 'El Alto' : 'La Paz';

            return [
                'name' => $row[0],
                'email' => Str::slug($row[0], '.').'@institucional.pil.bo',
                'phone' => (string) (22010000 + $index),
                'address' => $addresses[$index % count($addresses)],
                'city' => $city,
                'owner_first_name' => $owner[0],
                'owner_last_name_paterno' => $owner[1],
                'owner_last_name_materno' => $owner[2],
            ];
        }, $rows, array_keys($rows));
    }

    private function neighborhoodStores(): array
    {
        $names = [
            'Tienda La Canasta', 'Abarrotes Villa Victoria', 'Minimarket Los Pinos', 'Despensa San Miguel',
            'Tienda Don Lucho', 'Market Las Nieves', 'Abarrotes Nueva Esperanza', 'Tienda La Esquina',
            'Micromarket Achumani', 'Tienda Virgen de Copacabana', 'Abarrotes Primavera', 'Tienda El Buen Precio',
            'Mini Super Kantuta', 'Despensa Los Andes', 'Tienda San Antonio', 'Abarrotes Irpavi',
            'Market Sopocachi', 'Tienda Rosita', 'Despensa Calacoto', 'Tienda San Jorge',
        ];
        $zones = [
            ['La Paz', 'Av. 16 de Julio, Prado'], ['La Paz', 'Av. Arce, San Jorge'], ['La Paz', 'Calle 21, Calacoto'],
            ['La Paz', 'Av. Ballivian, San Miguel'], ['La Paz', 'Calle 29, Achumani'], ['La Paz', 'Av. Saavedra, Miraflores'],
            ['La Paz', 'Av. Buenos Aires, Sopocachi'], ['La Paz', 'Av. Busch, Villa Fatima'], ['La Paz', 'Av. Tejada Sorzano, Villa Copacabana'],
            ['La Paz', 'Av. Periferica, Munaypata'], ['El Alto', 'Av. Juan Pablo II, Rio Seco'], ['El Alto', 'Av. Alfonso Ugarte, 16 de Julio'],
            ['El Alto', 'Av. Antofagasta, Villa Dolores'], ['El Alto', 'Av. Bolivia, Ciudad Satelite'], ['El Alto', 'Av. Litoral, Villa Adela'],
            ['El Alto', 'Av. 6 de Marzo, Senkata'], ['El Alto', 'Av. Panoramica, Alto Lima'], ['El Alto', 'Av. Tiwanaku, Distrito 8'],
            ['El Alto', 'Av. Costanera, Mercedario'], ['El Alto', 'Av. Civica, Ceja'],
        ];
        $owners = $this->owners();
        $stores = [];

        for ($i = 0; $i < 100; $i++) {
            $zone = $zones[$i % count($zones)];
            $owner = $owners[($i + 7) % count($owners)];
            $stores[] = [
                'name' => $names[$i % count($names)].' '.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'email' => 'tienda.'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT).'@barrios.pil.bo',
                'phone' => (string) (71500000 + $i),
                'address' => $zone[1].', calle '.(($i % 12) + 1).' #'.(100 + $i),
                'city' => $zone[0],
                'owner_first_name' => $owner[0],
                'owner_last_name_paterno' => $owner[1],
                'owner_last_name_materno' => $owner[2],
            ];
        }

        return $stores;
    }

    private function owners(): array
    {
        return [
            ['Mariela', 'Quispe', 'Ramos'], ['Gustavo', 'Mamani', 'Flores'], ['Paola', 'Velasco', 'Rios'],
            ['Marcelo', 'Lopez', 'Mendez'], ['Viviana', 'Salvatierra', 'Pena'], ['Jorge', 'Paz', 'Beltran'],
            ['Andrea', 'Canelas', 'Quiroga'], ['Carlos', 'Arias', 'Duran'], ['Sandra', 'Gomez', 'Rocabado'],
            ['Hector', 'Villca', 'Rojas'], ['Miriam', 'Arnez', 'Soruco'], ['Rene', 'Mamani', 'Choque'],
            ['Nadia', 'Pinto', 'Sosa'], ['Veronica', 'Rojas', 'Torrico'], ['Gregorio', 'Quispe', 'Cruz'],
            ['Lucia', 'Apaza', 'Mendoza'], ['Felipe', 'Sanjines', 'Ortiz'], ['Sonia', 'Navarro', 'Paredes'],
            ['Wilson', 'Condori', 'Yanarico'], ['Patricia', 'Suarez', 'Montano'],
        ];
    }

    private function institutionalAddresses(): array
    {
        return [
            'Av. Ballivian y Calle 21, Calacoto',
            'Av. Montenegro, San Miguel',
            'Av. Hernando Siles, Obrajes',
            'Av. Arce, San Jorge',
            'Av. 16 de Julio, Centro',
            'Calle Sagarnaga, Centro',
            'Av. Saavedra, Miraflores',
            'Av. Camacho, Centro',
            'Calle 10, Achumani',
            'Av. Costanera, Bajo Seguencoma',
            'Av. Busch, Miraflores',
            'Calle 21 de Calacoto',
            'Av. Buenos Aires, Sopocachi',
            'Av. Mariscal Santa Cruz, Centro',
            'Av. Juan Pablo II, Rio Seco',
            'Av. 6 de Marzo, El Alto',
        ];
    }
}
