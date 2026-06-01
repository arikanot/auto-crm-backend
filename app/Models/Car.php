<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Car extends Model
{
    protected $fillable = ['client_id', 'brand', 'model', 'vin', 'number_place', 'year'];


    public function client() : BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
