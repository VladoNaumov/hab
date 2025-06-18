<!-- resources/views/checkout/create.blade.php -->

@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="container my-5">
        <h1 class="text-center mb-4">Checkout</h1>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Форма оформления заказа -->
                <form action="{{ route('checkout.store') }}" method="POST" class="card shadow-sm p-4">
                    @csrf

                    <!-- Личные данные -->
                    <h3 class="mb-3">Personal Information</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" required
                                   value="{{ old('first_name') }}" placeholder="Enter your first name">
                            @error('first_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" required
                                   value="{{ old('last_name') }}" placeholder="Enter your last name">
                            @error('last_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required
                               value="{{ old('email') }}" placeholder="example@domain.com"
                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                        @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Адрес доставки -->
                    <h3 class="mb-3 mt-4">Shipping Information</h3>
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" name="city" id="city" class="form-control" required
                               value="{{ old('city') }}" placeholder="Enter your city">
                        @error('city')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="postal_code" class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" id="postal_code" class="form-control" required
                               value="{{ old('postal_code') }}" placeholder="12345" pattern="[0-9]{5}">
                        @error('postal_code')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" name="address" id="address" class="form-control" required
                               value="{{ old('address') }}" placeholder="Street, house number">
                        @error('address')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone (Optional)</label>
                        <input type="tel" name="phone" id="phone" class="form-control"
                               value="{{ old('phone') }}" placeholder="+38 (999) 999-99-99"
                               pattern="\+?[0-9\s\-\(\)]{10,15}">
                        <small class="form-text text-muted">Optional, format: +38 (999) 999-99-99</small>
                        @error('phone')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Информация о заказе -->
                    <h3 class="mb-3 mt-4">Your Order</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Product</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($cartArray as $item)
                                <tr>
                                    <td>
                                        @if ($item['image'])
                                            <img src="{{ asset($item['image']) }}"
                                                 alt="{{ $item['name'] }}"
                                                 class="img-thumbnail"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <img src="https://via.placeholder.com/60" alt="No image"
                                                 class="img-thumbnail"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        @endif
                                    </td>
                                    <td>{{ $item['name'] ?? 'No name' }}</td>
                                    <td>{{ $item['description'] ?? 'No description' }}</td>
                                    <td>{{ number_format($item['discount_price'] ?? $item['price'], 2, '.', ' ') }} EUR</td>
                                    <td>{{ $item['quantity'] }}</td>
                                    <td>{{ number_format($item['total'], 2, '.', ' ') }} EUR</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Your cart is empty.</td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Total:</td>
                                <td class="fw-bold">{{ number_format($total, 2, '.', ' ') }} EUR</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Кнопки действия -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">Back to Cart</a>
                        <button type="submit" class="btn btn-primary">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
