@extends('layouts.app')

<!-- resources/views/products/show.blade.php -->

@section('title', htmlspecialchars($product->name))

@section('content')
    <h1 class="mb-4 text-center">Product</h1>

    <div class="container mt-4">
        <div class="product-card show-page">
            <!-- Изображение -->
            <div class="product-image">
                @if ($product->image)
                    <img src="{{ asset(htmlspecialchars('/' . $product->image)) }}" alt="{{ htmlspecialchars($product->name) }}" class="main-image">
                @else
                    <img src="https://via.placeholder.com/400x400" alt="No image" class="main-image">
                @endif
            </div>

            <div class="product-info">
                <h1 class="product-title"><strong>Product name:</strong> {{ htmlspecialchars($product->name) }}</h1>

                @if ($product->article)
                    <p class="product-article"><strong>Article:</strong> {{ htmlspecialchars($product->article) }}</p>
                @endif

                @if ($product->brand)
                    <p class="product-brand"><strong>Brand:</strong> {{ htmlspecialchars($product->brand) }}</p>
                @endif

                @if ($product->category)
                    <p class="product-category"><strong>Category:</strong> {{ htmlspecialchars($product->category) }}</p>
                @endif

                @if ($product->country)
                    <p class="product-country"><strong>Country:</strong> {{ htmlspecialchars($product->country) }}</p>
                @endif

                @if ($product->description)
                    <p class="product-description">{{ htmlspecialchars($product->description) }}</p>
                @else
                    <p class="product-description">No description available</p>
                @endif

                <!-- Цена и скидка -->
                <p class="product-price">
                    <strong>Price:</strong> {{ number_format($product->price ?? 0, 2, ',', ' ') }} EUR
                    @if ($product->discount_price)
                        <span class="product-discount-price">
                            (Discount: {{ number_format($product->discount_price, 2, ',', ' ') }} EUR)
                        </span>
                    @endif
                </p>

                <form id="add-to-cart-form" action="{{ route('cart.add', $product->brand) }}" method="POST">
                    @csrf
                    <div class="input-group mb-3 quantity-control">
                        <button type="button" class="btn btn-outline-secondary">−</button>
                        <input type="number" name="quantity" class="form-control text-center" value="1" min="1" max="10">
                        <button type="button" class="btn btn-outline-secondary">+</button>
                    </div>
                    <button type="submit" class="btn btn-primary add-to-cart-btn">Add to Cart</button>
                </form>
            </div>
        </div>

        <!-- Отзывы -->
        <div class="product-reviews mt-4">
            <h5>Customer Reviews</h5>
            <div class="star-rating">
                @php
                    $rating = round($product->rating, 1);
                    $fullStars = floor($rating);
                    $halfStar = $rating - $fullStars >= 0.5;
                    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                @endphp
                @for ($i = 0; $i < $fullStars; $i++)
                    ★
                @endfor
                @if ($halfStar)
                    ★½
                @endif
                @for ($i = 0; $i < $emptyStars; $i++)
                    ☆
                @endfor
                <span class="rating-value">({{ number_format($product->rating, 1) }})</span>
            </div>
        </div>
    </div>

    <!-- Стили -->
    <link href="{{ asset('css/product_show.css') }}" rel="stylesheet">

    <!-- Встроенный JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const minusBtn = document.querySelector('.quantity-control button:first-child');
            const plusBtn = document.querySelector('.quantity-control button:last-child');
            const quantityInput = document.querySelector('.quantity-control input[name="quantity"]');

            minusBtn.addEventListener('click', function () {
                let value = parseInt(quantityInput.value);
                if (value > parseInt(quantityInput.min)) {
                    quantityInput.value = value - 1;
                }
            });

            plusBtn.addEventListener('click', function () {
                let value = parseInt(quantityInput.value);
                if (value < parseInt(quantityInput.max)) {
                    quantityInput.value = value + 1;
                }
            });
        });
    </script>
@endsection
