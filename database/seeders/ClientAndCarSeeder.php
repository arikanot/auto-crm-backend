<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Car;

class ClientAndCarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Клиент 1 с одной машиной
        $client1 = Client::create([
            'name' => 'Константин Смирнов',
            'phone' => '+79991112233',
            'email' => 'smirnov@mail.ru',
            'comment' => 'Постоянный клиент, скидка 10% на работы.',
        ]);

        Car::create([
            'client_id' => $client1->id,
            'brand' => 'BMW',
            'model' => 'X5',
            'vin' => 'XW8ZZZ11122233344',
            'number_plate' => 'О777РР77',
            'year' => 2021,
        ]);

        // Клиент 2 с ДВУМЯ машинами (семейный автопарк)
        $client2 = Client::create([
            'name' => 'Елена Петрова',
            'phone' => '+79994445566',
            'email' => 'elena.p@yandex.ru',
            'comment' => 'Оплата всегда картой. Требует чистый салон.',
        ]);

        Car::create([
            'client_id' => $client2->id,
            'brand' => 'Toyota',
            'model' => 'RAV4',
            'vin' => 'JTMZZZ55566677788',
            'number_plate' => 'А123АА177',
            'year' => 2019,
        ]);

        Car::create([
            'client_id' => $client2->id,
            'brand' => 'Mini',
            'model' => 'Cooper',
            'vin' => 'WMWZZZ99988877766',
            'number_plate' => 'В456ВВ177',
            'year' => 2022,
        ]);
    }
}
