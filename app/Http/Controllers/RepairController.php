<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Repair;
use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;

class RepairController extends Controller
{

    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Repair::with(['car.client']);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%")
                  // Ищем по марке/модели машины внутри связи
                  ->orWhereHas('car', function (Builder $carQ) use ($search) {
                      $carQ->where('brand', 'LIKE', "%{$search}%")
                           ->orWhere('model', 'LIKE', "%{$search}%")
                           ->orWhere('number_plate', 'LIKE', "%{$search}%")
                           // Ищем по имени клиента внутри машины
                           ->orWhereHas('client', function (Builder $clientQ) use ($search) {
                               $clientQ->where('name', 'LIKE', "%{$search}%")
                                       ->orWhere('phone', 'LIKE', "%{$search}%");
                           });
                  });
            });
        }
        $query->latest();

        return response()->json($query->paginate(15));
    }



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
