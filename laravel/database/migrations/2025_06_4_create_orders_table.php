<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создаёт таблицу заказов
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->after('id'); // Обязательное, уникальное
            $table->string('session_id')->index()->after('id'); //  Обязательное, с индексом
            $table->string('first_name')->index(); // Обязательное, с индексом
            $table->string('last_name')->index(); // Обязательное, с индексом
            $table->string('email'); // Обязательное
            $table->string('city'); // Обязательное
            $table->string('postal_code'); // Обязательное
            $table->text('address'); // Обязательное
            $table->string('phone')->nullable(); // Не обязательное
            $table->decimal('total', 10, 2); // Обязательное
            $table->string('status')->default('pending'); // Не обязательное, с дефолтным значением
            $table->timestamps();
        });
    }

    /**
     * Удаляет таблицу заказов
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
