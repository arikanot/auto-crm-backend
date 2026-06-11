<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Repair extends Model
{
    use HasFactory;

    protected $fillable = ['car_id', 'description', 'status', 'labor_cost', 'parts_cost', 'notes'];
    protected $with = ['parts'];
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function parts()
    {
        return $this->belongsToMany(Part::class, 'part_repair')
                    ->withPivot('quantity', 'price_at_sale')
                    ->withTimestamps();
    }
}
