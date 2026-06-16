<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed minimal : uniquement les comptes applicatifs.
     * Clients, produits, commandes et incidents proviennent de Cloud Firestore.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin C7pourt3',
            'email' => 'magnagamakelighiclainn@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Caissier C7pourt3',
            'email' => 'caissier@c7pourt3.com',
            'password' => bcrypt('password'),
            'role' => 'caissier',
        ]);
    }
}
