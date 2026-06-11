<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            EventSeeder::class,
            RegistrationSeeder::class,
            ReviewSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
