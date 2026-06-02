<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Repair extends Model
{
    use HasFactory;

    protected $fillable = ['car_id', 'description', 'status', 'labor_cost', 'parts_cost', 'notes'];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
