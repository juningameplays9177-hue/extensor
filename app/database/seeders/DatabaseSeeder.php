<?php

namespace Database\Seeders;

use App\Models\Container;
use App\Models\Depot;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@toprio.com',
        ], [
            'name' => 'Admin Top Rio',
            'password' => Hash::make('12345678'),
            'role' => User::ROLE_ADMIN,
        ]);

        $mainDepot = Depot::query()->firstOrCreate(
            ['name' => 'Deposito Central'],
            ['address' => 'Rua Principal, 1000', 'is_active' => true]
        );

        $eastDepot = Depot::query()->firstOrCreate(
            ['name' => 'Deposito Leste'],
            ['address' => 'Avenida Leste, 220', 'is_active' => true]
        );

        foreach (range(1, 8) as $index) {
            Container::query()->firstOrCreate(
                ['identifier' => 'CX-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT)],
                [
                    'depot_id' => $index <= 4 ? $mainDepot->id : $eastDepot->id,
                    'status' => Container::STATUS_AVAILABLE,
                ]
            );
        }
    }
}
