<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       return response()->json(
            Client::with('cars')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:clients,phone',
            'email' => 'nullable|email|max:255',
            'comment' => 'nullable|string',

            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'number_plate' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:1900|max:' . date('Y'),
        ],[
            'phone.unique' => 'Клиент с таким номером телефона уже существует.',
            'name.required' => 'Имя клиента обязательно для заполнения.',
            'brand.required' => 'Марка машины обязательна.',
            'model.required' => 'Модель машины обязательна.',
        ]);

        return DB::transaction(function () use ($validated) {
            $client = Client::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'comment' => $validated['comment'] ?? null,
            ]);
            $client->cars()->create([
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'number_plate' => $validated['number_plate'] ?? null,
                'year' => $validated['year'] ?? null,
            ]);

            return response()->json($client->load('cars'), 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        //
    }
}
