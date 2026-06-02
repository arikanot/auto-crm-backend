<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'admin@auto.com'], // По какому полю искать
            [
                'name' => 'Иван Админ',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Создаем Менеджера (приемщика заказов)
        // User::create([
        //     'name' => 'Алексей Менеджер',
        //     'email' => 'manager@auto.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'manager',
        // ]);

        // // Создаем Механика (мастера)
        // User::create([
        //     'name' => 'Петр Автомеханик',
        //     'email' => 'mechanic@auto.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'mechanic',
        // ]);

        $this->call(ClientAndCarSeeder::class);
    }

}
