<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $fillable = ['name', 'sku', 'brand', 'stock_quantity', 'purchase_price', 'selling_price', 'location'];


    public function repairs()
    {
        return $this->belongsToMany(Repair::class, 'part_repair')
        ->wherePivot('quantity', 'price_at_sale')
        ->withTimestamps();
    }
}
