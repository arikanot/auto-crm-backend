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
    // 1. Валидируем данные, включая массив запчастей
    $validated = $request->validate([
        'car_id' => 'required|exists:cars,id',
        'description' => 'required|string|max:255',
        'labor_cost' => 'required|numeric|min:0',
        'notes' => 'nullable|string',
        'status' => 'required|in:pending,in_progress,waiting_parts,completed',
        // Ожидаем массив запчастей с фронтенда
        'parts' => 'nullable|array',
        'parts.*.id' => 'required|exists:parts,id',
        'parts.*.quantity' => 'required|integer|min:1',
    ]);

    // 2. Запускаем базу данных в режиме транзакции
    return DB::transaction(function () use ($validated) {
        $partsCost = 0;
        $partsToAttach = [];

        // 3. Перебираем запчасти, считаем сумму и списываем со склада
        if (!empty($validated['parts'])) {
            foreach ($validated['parts'] as $item) {
                $part = Part::find($item['id']);

                // Проверяем, хватает ли остатка на складе
                if ($part->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'message' => "Недостаточно товара на складе: {$part->name}. Остаток: {$part->stock_quantity} шт."
                    ], 422);
                }

                // Списываем остаток
                $part->stock_quantity -= $item['quantity'];
                $part->save();

                // Плюсуем стоимость этой позиции к общей стоимости запчастей
                $partsCost += $part->selling_price * $item['quantity'];

                // Готовим данные для записи в pivot-таблицу part_repair
                $partsToAttach[$part->id] = [
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $part->selling_price
                ];
            }
        }

        // 4. Создаем сам заказ-наряд в базе
        $repair = Repair::create([
            'car_id' => $validated['car_id'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'labor_cost' => $validated['labor_cost'],
            'parts_cost' => $partsCost, // Сохраняем честно посчитанную сумму за детали
            'notes' => $validated['notes'] ?? null,
        ]);

        // 5. Железобетонно привязываем запчасти к созданному ремонту
        if (!empty($partsToAttach)) {
            $repair->parts()->attach($partsToAttach);
        }

        return response()->json($repair->load('parts'), 201);
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

   public function update(Request $request, string $id)
    {
        $repair = Repair::with('parts')->find($id);
        if (!$repair) {
            return response()->json(['message' => 'Заказ-наряд не найден'], 404);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'labor_cost' => 'required|numeric|min:0',
            'status' => 'required|in:pending,in_progress,waiting_parts,completed',
            'notes' => 'nullable|string',
            // Мы передаем новый массив запчастей, который полностью заменит старый
            'parts' => 'nullable|array',
            'parts.*.id' => 'required|exists:parts,id',
            'parts.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($repair, $validated) {
            // 1. Возвращаем ВСЕ старые запчасти этого ремонта обратно на склад
            foreach ($repair->parts as $oldPart) {
                $oldPart->stock_quantity += $oldPart->pivot->quantity;
                $oldPart->save();
            }

            // Отвязываем старые запчасти в pivot-таблице
            $repair->parts()->detach();

            // 2. Проверяем и списываем НАБОР НОВЫХ запчастей
            $partsCost = 0;
            $partsToAttach = [];

            if (!empty($validated['parts'])) {
                foreach ($validated['parts'] as $item) {
                    $part = Part::find($item['id']);

                    if ($part->stock_quantity < $item['quantity']) {
                        return response()->json([
                            'message' => "Недостаточно товара на складе для обновления: {$part->name}. Остаток: {$part->stock_quantity} шт."
                        ], 422);
                    }

                    // Списываем новый остаток
                    $part->stock_quantity -= $item['quantity'];
                    $part->save();

                    $partsCost += $part->selling_price * $item['quantity'];

                    $partsToAttach[$part->id] = [
                        'quantity' => $item['quantity'],
                        'price_at_sale' => $part->selling_price
                    ];
                }
            }

            // 3. Обновляем сам заказ-наряд
            $repair->update([
                'description' => $validated['description'],
                'status' => $validated['status'],
                'labor_cost' => $validated['labor_cost'],
                'parts_cost' => $partsCost, // Пересчитанная сумма за новые запчасти
                'notes' => $validated['notes'] ?? null,
            ]);

            // Привязываем новые детали
            if (!empty($partsToAttach)) {
                $repair->parts()->attach($partsToAttach);
            }

            return response()->json($repair->load('car.client', 'parts'));
        });
    }

    public function destroy(string $id)
    {
        $repair = Repair::with('parts')->find($id);
        if (!$repair) {
            return response()->json(['message' => 'Заказ-наряд не найден'], 404);
        }

        DB::transaction(function () use ($repair) {
            // Возвращаем все запчасти на склад перед удалением заказа
            foreach ($repair->parts as $part) {
                $part->stock_quantity += $part->pivot->quantity;
                $part->save();
            }

            // Удаляем сам заказ-наряд (связи в part_repair удалятся каскадно благодаря onDelete('cascade'))
            $repair->delete();
        });

        return response()->json(['message' => 'Заказ-наряд успешно удален, запчасти возвращены на склад']);
    }
}
