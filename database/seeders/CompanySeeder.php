<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = User::where('email', 'admin@gmail.com')->value('id')
            ?? User::whereHas('role', fn ($query) => $query->where('name', 'Administrador'))->value('id')
            ?? User::query()->value('id')
            ?? 1;

        $companies = [
            ['company_type' => 'empresa_institucional', 'name' => 'Hipermaxi Irpavi', 'nit' => '901000001', 'email' => 'compras.irpavi@hipermaxi.bo', 'phone' => '22100001', 'address' => 'Av. Ballivian, Calacoto', 'city' => 'La Paz', 'owner_first_name' => 'Paola', 'owner_last_name_paterno' => 'Velasco', 'owner_last_name_materno' => 'Rios'],
            ['company_type' => 'empresa_institucional', 'name' => 'Ketal Obrajes', 'nit' => '901000002', 'email' => 'abastecimiento.obrajes@ketal.bo', 'phone' => '22100002', 'address' => 'Av. Hernando Siles, Obrajes', 'city' => 'La Paz', 'owner_first_name' => 'Marcelo', 'owner_last_name_paterno' => 'Lopez', 'owner_last_name_materno' => 'Mendez'],
            ['company_type' => 'empresa_institucional', 'name' => 'Fidalga Equipetrol', 'nit' => '901000003', 'email' => 'compras.equipetrol@fidalga.bo', 'phone' => '33100003', 'address' => 'Av. San Martin, Equipetrol', 'city' => 'Santa Cruz', 'owner_first_name' => 'Viviana', 'owner_last_name_paterno' => 'Salvatierra', 'owner_last_name_materno' => 'Pena'],
            ['company_type' => 'empresa_institucional', 'name' => 'IC Norte Cochabamba', 'nit' => '901000004', 'email' => 'compras@icnorte.bo', 'phone' => '44100004', 'address' => 'Av. America y Pando', 'city' => 'Cochabamba', 'owner_first_name' => 'Jorge', 'owner_last_name_paterno' => 'Paz', 'owner_last_name_materno' => 'Beltran'],
            ['company_type' => 'empresa_institucional', 'name' => 'Supermercado Slan Sopocachi', 'nit' => '901000005', 'email' => 'sopocachi@slan.bo', 'phone' => '22100005', 'address' => 'Av. 6 de Agosto, Sopocachi', 'city' => 'La Paz', 'owner_first_name' => 'Andrea', 'owner_last_name_paterno' => 'Canelas', 'owner_last_name_materno' => 'Quiroga'],
            ['company_type' => 'empresa_institucional', 'name' => 'Hipermaxi Norte', 'nit' => '901000006', 'email' => 'compras.norte@hipermaxi.bo', 'phone' => '33100006', 'address' => 'Av. Banzer, 4to anillo', 'city' => 'Santa Cruz', 'owner_first_name' => 'Carlos', 'owner_last_name_paterno' => 'Arias', 'owner_last_name_materno' => 'Duran'],
            ['company_type' => 'empresa_institucional', 'name' => 'Hotel Camino Real La Paz', 'nit' => '901000007', 'email' => 'abastecimiento@caminoreal.com.bo', 'phone' => '22100007', 'address' => 'Av. Ballivian, Calacoto', 'city' => 'La Paz', 'owner_first_name' => 'Sandra', 'owner_last_name_paterno' => 'Gomez', 'owner_last_name_materno' => 'Rocabado'],
            ['company_type' => 'empresa_institucional', 'name' => 'Pollos Copacabana Central', 'nit' => '901000008', 'email' => 'compras@copacabana.com.bo', 'phone' => '22100008', 'address' => 'Av. Camacho', 'city' => 'La Paz', 'owner_first_name' => 'Hector', 'owner_last_name_paterno' => 'Villca', 'owner_last_name_materno' => 'Rojas'],
            ['company_type' => 'empresa_institucional', 'name' => 'Universidad Privada Boliviana Comedor', 'nit' => '901000009', 'email' => 'comedor@upb.edu', 'phone' => '44100009', 'address' => 'Av. Capitan Ustariz Km 6', 'city' => 'Cochabamba', 'owner_first_name' => 'Miriam', 'owner_last_name_paterno' => 'Arnez', 'owner_last_name_materno' => 'Soruco'],
            ['company_type' => 'empresa_institucional', 'name' => 'Hospital del Norte', 'nit' => '901000010', 'email' => 'abastecimiento@hospitalnorte.bo', 'phone' => '28100010', 'address' => 'Av. Juan Pablo II', 'city' => 'El Alto', 'owner_first_name' => 'Rene', 'owner_last_name_paterno' => 'Mamani', 'owner_last_name_materno' => 'Choque'],
            ['company_type' => 'empresa_institucional', 'name' => 'Panaderia Victoria Food Service', 'nit' => '901000011', 'email' => 'compras@victoriafs.bo', 'phone' => '33100011', 'address' => 'Parque Industrial, modulo 4', 'city' => 'Santa Cruz', 'owner_first_name' => 'Nadia', 'owner_last_name_paterno' => 'Pinto', 'owner_last_name_materno' => 'Sosa'],
            ['company_type' => 'empresa_institucional', 'name' => 'Catering Sabores de Mi Tierra', 'nit' => '901000012', 'email' => 'pedidos@saboresmitierra.bo', 'phone' => '44100012', 'address' => 'Av. Melchor Perez', 'city' => 'Cochabamba', 'owner_first_name' => 'Veronica', 'owner_last_name_paterno' => 'Rojas', 'owner_last_name_materno' => 'Torrico'],
            ['company_type' => 'tienda_barrio', 'name' => 'Tienda Don Goyo', 'nit' => '901000013', 'email' => 'dongoyo@tiendas.bo', 'phone' => '71500013', 'address' => 'Zona Villa Fatima, calle final', 'city' => 'La Paz', 'owner_first_name' => 'Gregorio', 'owner_last_name_paterno' => 'Quispe', 'owner_last_name_materno' => 'Cruz'],
            ['company_type' => 'tienda_barrio', 'name' => 'Minimarket 16 de Julio', 'nit' => '901000014', 'email' => '16dejulio@tiendas.bo', 'phone' => '71500014', 'address' => 'Av. Juan Pablo II, esquina feria', 'city' => 'El Alto', 'owner_first_name' => 'Lucia', 'owner_last_name_paterno' => 'Apaza', 'owner_last_name_materno' => 'Mendoza'],
            ['company_type' => 'tienda_barrio', 'name' => 'Despensa La Familia', 'nit' => '901000015', 'email' => 'lafamilia@tiendas.bo', 'phone' => '71500015', 'address' => 'Barrio Hamacas, calle 7', 'city' => 'Santa Cruz', 'owner_first_name' => 'Roberto', 'owner_last_name_paterno' => 'Justiniano', 'owner_last_name_materno' => 'Ribera'],
            ['company_type' => 'tienda_barrio', 'name' => 'Abarrotes San Jose', 'nit' => '901000016', 'email' => 'sanjose@tiendas.bo', 'phone' => '71500016', 'address' => 'Zona Villa Busch, pasaje 3', 'city' => 'Cochabamba', 'owner_first_name' => 'Maria', 'owner_last_name_paterno' => 'Veizaga', 'owner_last_name_materno' => 'Tola'],
            ['company_type' => 'tienda_barrio', 'name' => 'Tienda La Esquina', 'nit' => '901000017', 'email' => 'laesquina@tiendas.bo', 'phone' => '71500017', 'address' => 'Achumani, calle 15', 'city' => 'La Paz', 'owner_first_name' => 'Felipe', 'owner_last_name_paterno' => 'Sanjines', 'owner_last_name_materno' => 'Ortiz'],
            ['company_type' => 'tienda_barrio', 'name' => 'Micromarket Los Pinos', 'nit' => '901000018', 'email' => 'lospinos@tiendas.bo', 'phone' => '71500018', 'address' => 'Calacoto, calle 23', 'city' => 'La Paz', 'owner_first_name' => 'Sonia', 'owner_last_name_paterno' => 'Navarro', 'owner_last_name_materno' => 'Paredes'],
            ['company_type' => 'tienda_barrio', 'name' => 'Kiosko Nuevo Horizonte', 'nit' => '901000019', 'email' => 'horizonte@tiendas.bo', 'phone' => '71500019', 'address' => 'Distrito 8, avenida principal', 'city' => 'El Alto', 'owner_first_name' => 'Wilson', 'owner_last_name_paterno' => 'Condori', 'owner_last_name_materno' => 'Yanarico'],
            ['company_type' => 'tienda_barrio', 'name' => 'Market El Buen Precio', 'nit' => '901000020', 'email' => 'buenprecio@tiendas.bo', 'phone' => '71500020', 'address' => 'Plan 3000, modulo 5', 'city' => 'Santa Cruz', 'owner_first_name' => 'Patricia', 'owner_last_name_paterno' => 'Suarez', 'owner_last_name_materno' => 'Montaño'],
            ['company_type' => 'tienda_barrio', 'name' => 'Abarrotes Tiquipaya', 'nit' => '901000021', 'email' => 'tiquipaya@tiendas.bo', 'phone' => '71500021', 'address' => 'Av. Reducto, Tiquipaya', 'city' => 'Cochabamba', 'owner_first_name' => 'Edwin', 'owner_last_name_paterno' => 'Terrazas', 'owner_last_name_materno' => 'Camacho'],
            ['company_type' => 'tienda_barrio', 'name' => 'Tienda Virgen de Copacabana', 'nit' => '901000022', 'email' => 'copacabana@tiendas.bo', 'phone' => '71500022', 'address' => 'Villa Adela, avenida principal', 'city' => 'El Alto', 'owner_first_name' => 'Julia', 'owner_last_name_paterno' => 'Huanca', 'owner_last_name_materno' => 'Ramos'],
        ];

        foreach ($companies as $data) {
            $cityId = City::where('name', $data['city'])->value('id');

            Company::updateOrCreate(
                ['nit' => $data['nit']],
                [
                    'company_type' => $data['company_type'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'city_id' => $cityId,
                    'owner_first_name' => $data['owner_first_name'],
                    'owner_last_name_paterno' => $data['owner_last_name_paterno'],
                    'owner_last_name_materno' => $data['owner_last_name_materno'],
                    'created_by' => $creatorId,
                    'google_maps_url' => null,
                ]
            );
        }
    }
}
