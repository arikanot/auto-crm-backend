<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Repair;
use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Part;
use Illuminate\Support\Facades\DB;

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
    // 1. Валидируем входящие данные
    $validated = $request->validate([
        'car_id' => 'required|exists:cars,id',
        'description' => 'required|string|max:255',
        'labor_cost' => 'required|numeric|min:0',
        'notes' => 'nullable|string',
        'status' => 'required|in:pending,in_progress,waiting_parts,completed',
        // Запчасти передаются в виде массива: [['id' => 1, 'quantity' => 2], ...]
        'parts' => 'nullable|array',
        'parts.*.id' => 'required|exists:parts,id',
        'parts.*.quantity' => 'required|integer|min:1',
    ]);

    // 2. Обернем всё в транзакцию базы данных (если склад не обновится, то и ремонт не создастся)
    return DB::transaction(function () use ($validated) {

        $partsCost = 0;
        $partsToAttach = [];

        // 3. Проверяем остатки на складе и рассчитываем общую стоимость запчастей
        if (!empty($validated['parts'])) {
            foreach ($validated['parts'] as $item) {
                $part = Part::find($item['id']);

                // Проверяем, хватает ли деталей на складе
                if ($part->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'message' => "Недостаточно товара на складе: {$part->name}. Остаток: {$part->stock_quantity} шт."
                    ], 422);
                }

                // Уменьшаем остаток на складе
                $part->stock_quantity -= $item['quantity'];
                $part->save();

                // Считаем стоимость для клиента
                $partsCost += $part->selling_price * $item['quantity'];

                // Формируем массив для связи во многие-ко-многим
                $partsToAttach[$part->id] = [
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $part->selling_price
                ];
            }
        }

        // 4. Создаем заказ-наряд
        $repair = Repair::create([
            'car_id' => $validated['car_id'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'labor_cost' => $validated['labor_cost'],
            'parts_cost' => $partsCost, // Стоимость запчастей рассчиталась автоматически!
            'notes' => $validated['notes'] ?? null,
        ]);

        // 5. Привязываем запчасти к ремонту в pivot-таблицу
        if (!empty($partsToAttach)) {
            $repair->parts()->attach($partsToAttach);
        }

        return response()->json($repair->load('car.client', 'parts'), 201);
    });
}

    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,waiting_parts,completed',
        ]);

        $repair = Repair::find($id);

        if (!$repair) {
            return response()->json(['message' => 'Заказ не найден'], 404);
        }

        $repair->status = $validated['status'];
        $repair->save();

        return response()->json($repair->load('car.client'));

    }
}
