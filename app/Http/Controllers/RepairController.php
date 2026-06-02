<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Repair;
use App\Models\Car;

class RepairController extends Controller
{
    public function store(Request $request)
    {
       $validated = $request->validate([
            'car_id' => 'required|exists:cars,id', // Проверяем, что машина реально существует
            'description' => 'required|string|max:255',
            'status' => 'required|in:pending,in_progress,waiting_parts,completed',
            'labor_cost' => 'nullable|numeric|min:0',
            'parts_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ], [
            'description.required' => 'Описание поломки или работ обязательно.',
        ]);

        // 2. Создаем запись в таблице repairs
        $repair = Repair::create([
            'car_id' => $validated['car_id'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'labor_cost' => $validated['labor_cost'] ?? 0,
            'parts_cost' => $validated['parts_cost'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json($repair, 201);
    }
}
