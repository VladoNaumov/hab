<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{brand}', [ProductController::class, 'show'])->name('products.show'); // Используем {brand}

    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

    Route::middleware('web')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/orders/{order_number}', [OrderController::class, 'show'])->name('orders.show'); // Используем {order_number}
    });
});

//Route::post('/orders/find', [OrderController::class, 'findByCode'])->name('orders.findByCode');


// TODO Рекомендуемые улучшения:
// - Интеграция платёжных шлюзов (например, Stripe, PayPal)
// - Email-уведомления о заказе пользователю и администратору
