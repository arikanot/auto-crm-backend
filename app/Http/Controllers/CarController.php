<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() : JsonResponse
    {
        $clients = Client::with('cars')->latest()->get();

        return response()->json($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) : JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id', // Проверяем, что владелец существует
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'number_plate' => 'nullable|string|max:20',
            'vin' => 'nullable|string|max:17',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ], [
            'brand.required' => 'Марка автомобиля обязательна.',
            'model.required' => 'Модель автомобиля обязательна.',
        ]);

        // 2. Создаем запись в таблице cars
        $car = Car::create([
            'client_id' => $validated['client_id'],
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'number_plate' => !empty($validated['number_plate']) ? mb_strtoupper($validated['number_plate']) : null, // Переводим госномер в верхний регистр
            'vin' => !empty($validated['vin']) ? mb_strtoupper($validated['vin']) : null,
            'year' => $validated['year'] ?? null,
        ]);

        // 3. Возвращаем созданное авто с кодом 201
        return response()->json($car, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Car $car)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Car $car)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Car $car)
    {
        //
    }
}
