<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создаёт таблицу товаров
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('article', 50)->unique(); // Обязательное, уникальное
            $table->string('name')->index(); // Обязательное, с индексом для поиска
            $table->string('brand')->unique()->index(); // Обязательное, уникальное, с индексом
            $table->string('category')->index(); // Обязательное, с индексом
            $table->text('description');
            $table->text('delivery')->nullable(); // Не обязательное;
            $table->string('country')->nullable(); // Не обязательное
            $table->decimal('price', 10, 2); // Обязательное
            $table->decimal('discount_price', 10, 2)->nullable(); // Не обязательное
            $table->string('image')->nullable(); // Не обязательное
            $table->timestamps();
        });
    }

    /**
     * Удаляет таблицу товаров
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
