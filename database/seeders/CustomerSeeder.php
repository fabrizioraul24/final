<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $buyerRoleId = Role::where('name', 'Comprador')->value('id');
        if (! $buyerRoleId) {
            $this->command?->warn('No existe el rol Comprador para CustomerSeeder.');
            return;
        }

        $customers = [
            ['name' => 'Daniela Quispe', 'email' => 'daniela.quispe@demo.bo', 'username' => 'daniela.quispe', 'nit' => '5600101', 'city' => 'La Paz', 'address' => 'Av. Arce, Edif. Libertad, depto 4B'],
            ['name' => 'Mauricio Rojas', 'email' => 'mauricio.rojas@demo.bo', 'username' => 'mauricio.rojas', 'nit' => '5600102', 'city' => 'La Paz', 'address' => 'Calle 21 de Calacoto, condominio Los Pinos'],
            ['name' => 'Luciana Mamani', 'email' => 'luciana.mamani@demo.bo', 'username' => 'luciana.mamani', 'nit' => '5600103', 'city' => 'El Alto', 'address' => 'Av. Juan Pablo II, zona Villa Dolores'],
            ['name' => 'Gabriel Flores', 'email' => 'gabriel.flores@demo.bo', 'username' => 'gabriel.flores', 'nit' => '5600104', 'city' => 'El Alto', 'address' => 'Zona 16 de Julio, calle A'],
            ['name' => 'Paola Cardenas', 'email' => 'paola.cardenas@demo.bo', 'username' => 'paola.cardenas', 'nit' => '5600105', 'city' => 'Santa Cruz', 'address' => 'Av. Beni, condominio Las Palmas'],
            ['name' => 'Jose Luis Saucedo', 'email' => 'jose.saucedo@demo.bo', 'username' => 'jose.saucedo', 'nit' => '5600106', 'city' => 'Santa Cruz', 'address' => 'Barrio Equipetrol Norte, torre 7'],
            ['name' => 'Valeria Soria', 'email' => 'valeria.soria@demo.bo', 'username' => 'valeria.soria', 'nit' => '5600107', 'city' => 'Cochabamba', 'address' => 'Av. America Oeste, edificio Toscana'],
            ['name' => 'Fernando Torrico', 'email' => 'fernando.torrico@demo.bo', 'username' => 'fernando.torrico', 'nit' => '5600108', 'city' => 'Cochabamba', 'address' => 'Tiquipaya, zona Linde'],
            ['name' => 'Martha Veizaga', 'email' => 'martha.veizaga@demo.bo', 'username' => 'martha.veizaga', 'nit' => '5600109', 'city' => 'La Paz', 'address' => 'Achumani, calle 29'],
            ['name' => 'Nicolas Vargas', 'email' => 'nicolas.vargas@demo.bo', 'username' => 'nicolas.vargas', 'nit' => '5600110', 'city' => 'Santa Cruz', 'address' => 'Doble Via La Guardia, zona oeste'],
        ];

        foreach ($customers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'password' => Hash::make('comprador1234'),
                    'role_id' => $buyerRoleId,
                ]
            );

            $cityId = City::where('name', $data['city'])->value('id');

            Customer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nit' => $data['nit'],
                    'delivery_address' => $data['address'],
                    'city' => $data['city'],
                    'city_id' => $cityId,
                ]
            );
        }
    }
}
