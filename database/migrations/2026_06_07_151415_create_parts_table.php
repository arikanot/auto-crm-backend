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
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); //Название
            $table->string('sku')->unique(); //Артикул/парту-номер
            $table->string('brand')->nullable();  //Производитель
            $table->integer('stock_quantity')->default(0); //Остаток на складе
            $table->decimal('purchase_price', 10, 2); //Цена закупки на СТО
            $table->decimal('selling_price', 10, 2); //Цена продажи клиенту
            $table->string('location')->nullable(); //Местро хранения
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
