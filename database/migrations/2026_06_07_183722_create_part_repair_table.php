<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('part_repair', function (Blueprint $table) {
           $table->id();
            $table->foreignId('repair_id')->constrained()->onDelete('cascade');
            $table->foreignId('part_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1); // Сколько штук списано в этот ремонт
            $table->decimal('price_at_sale', 10, 2); // Фиксируем цену продажи (на случай, если на складе она изменится позже)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_repair');
    }
};
