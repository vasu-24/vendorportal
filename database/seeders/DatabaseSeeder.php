<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            TravelEmployeeSeeder::class,
            AdminUserSeeder::class,
            MailTemplateSeeder::class,

        ]);
    }
}