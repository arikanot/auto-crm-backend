<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Part;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Part::query();

        if (!empty($search)) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('brand', 'LIKE', "%{$search}%");
        }

        return response()->json($query->orderBy('name')->paginate(15));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:parts,sku',
            'brand' => 'nullable|string|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:100',
        ]);

        $part = Part::create($validated);

        return response()->json($part, 201);
    }
}
