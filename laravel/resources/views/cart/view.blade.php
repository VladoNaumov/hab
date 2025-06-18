<!-- Корзина - Cart -->
<!-- resources/views/cart/view.blade.php -->

@extends('layouts.app')

@section('title', 'Cart')

@section('content')
    <h1 class="mb-4 text-center">Your Cart</h1>
    <div id="cart-content">
        @if (count($cart) > 0)
            <!-- Таблица с товарами в корзине -->
            <div class="table-responsive">
                <table class="table table-bordered" id="cart-table">
                    <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($cart as $id => $item)
                        <tr data-id="{{ $id }}">
                            <!-- Изображение товара -->
                            <td>
                                @if ($item['image'])
                                    <!-- NEW: Защита от XSS -->
                                    <img src="{{ asset('/' . htmlspecialchars($item['image'])) }}" class="cart-image"
                                         alt="{{ htmlspecialchars($item['name']) }}">
                                @else
                                    <img src="https://via.placeholder.com/100x100" class="cart-image" alt="No image">
                                @endif
                            </td>
                            <!-- Название -->
                            <td>{{ $item['name'] }}</td>
                            <!-- Цена -->
                            <!-- NEW: Учитываем скидку -->
                            <td>{{ number_format($item['discount_price'] ?? $item['price'], 2, ',', ' ') }} EUR</td>
                            <!-- Контроль количества -->
                            <td>
                                <div class="input-group quantity-control">
                                    <!-- NEW: Добавлены data-id для JS -->
                                    <button type="button" class="btn btn-outline-secondary decrement-btn"
                                            data-id="{{ $id }}">−</button>
                                    <input type="number" class="form-control text-center quantity-input"
                                           value="{{ $item['quantity'] }}" min="1"
                                           max="{{ config('shop.max_quantity_per_item', 10) }}" readonly
                                           data-id="{{ $id }}">
                                    <button type="button" class="btn btn-outline-secondary increment-btn"
                                            data-id="{{ $id }}">+</button>
                                </div>
                            </td>

                            <!-- NEW: Используем $item['total'] для  умножения -->
                            <td class="item-total" data-id="{{ $id }}">{{ number_format($item['total'], 2, ',', ' ') }} EUR</td>
                            <!-- Удаление товара -->
                            <td>
                                <form action="{{ route('cart.remove', $id) }}" method="POST" class="remove-form">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Общая сумма по корзине -->
            <!-- NEW: Используем $cartTotal -->
            <div class="text-end mt-4">
                <h4 id="cart-total">
                    Total: {{ number_format($cartTotal, 2, ',', ' ') }} EUR
                </h4>
            </div>
            <!-- Кнопки действия -->
            <div class="d-flex justify-content-end gap-2 mt-3 mb-5">
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back to Products</a>
                <a href="{{ route('checkout.create') }}" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        @else
            <!-- Пустая корзина -->
            <div class="empty-cart">
                <p>Your cart is empty.</p>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back to Products</a>
            </div>
        @endif
    </div>
    <!-- NEW: Подключение JS -->
    <script src="{{ asset('js/cart.js') }}"></script>
    <link href="{{ asset('css/cart.css') }}" rel="stylesheet">
@endsection
