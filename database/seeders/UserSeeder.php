<?php

namespace Database\Seeders;

use App\Models\Roles;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Roles::create([
            'role' => 'Super Admin',
            'slug' => 'super-admin',
            'parent_id' => null,
        ]);

        Roles::create([
            'role' => 'Admin',
            'slug' => 'admin',
            'parent_id' => null,
        ]);
    }
}
