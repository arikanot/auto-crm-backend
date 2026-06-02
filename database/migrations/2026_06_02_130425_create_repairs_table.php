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
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->enum('status', ['pending', 'in_progress', 'waiting_parts', 'completed'])->default('pending');
            $table->decimal('labor_cost', 10, 2)->default(0.00);//работа мастера
            $table->decimal('parts_cost', 10, 2)->default(0.00);//запчасти
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
