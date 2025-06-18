<!-- resources/views/products/index.blade.php -->

@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <link href="{{ asset('css/card.css') }}" rel="stylesheet"> <!-- Сохранён оригинальный CSS -->

    <h1 class="mb-3 text-center">Our Products</h1>

    <div class="row" id="products-top">
        @foreach ($products as $product)
            <div class="col-md-3 mb-4"> <!-- Сохранён col-md-3 -->
                <a href="{{ route('products.show', $product->brand) }}" class="text-decoration-none"> <!-- NEW: Используем brand вместо id -->
                    <div class="card shadow-sm h-100">
                        <!-- Изображение -->
                        <img src="{{ $product->image ? asset(htmlspecialchars('/' . $product->image)) : 'https://via.placeholder.com/200' }}"
                             class="card-img-top" alt="{{ htmlspecialchars($product->name) }}">

                        <!-- Тело карточки -->
                        <div class="card-body text-center">
                            <!-- Артикул -->
                            <p class="product-article">Article: {{ htmlspecialchars($product->article) }}</p> <!-- NEW: Добавлен артикул -->

                            <!-- Бренд -->
                            @if ($product->brand)
                                <p class="product-brand"><strong>{{ htmlspecialchars($product->brand) }}</strong></p>
                            @else
                                <p class="product-brand text-danger"><strong>Brand: MISSING</strong></p> <!-- NEW: Отладка -->
                            @endif

                            <!-- Название -->
                            <h5 class="product-info">{{ htmlspecialchars($product->name) }}</h5>

                            <!-- Категория -->
                            @if ($product->category)
                                <p class="product-category">{{ htmlspecialchars($product->category) }}</p>
                            @endif

                            <!-- Страна -->
                            <p class="product-country">Country: {{ htmlspecialchars($product->country) }}</p> <!-- NEW: Добавлена страна -->

                            <!-- Описание (краткое) -->
                            <p class="product-description">{{ htmlspecialchars(Str::limit($product->description, 50)) }}</p> <!-- NEW: Краткое описание -->

                            <!-- Цена и скидка -->
                            <p class="product-price">
                                {{ number_format($product->price, 2, ',', ' ') }} EUR
                                @if ($product->discount_price)
                                    <span class="product-discount-price">
                                    ({{ number_format($product->discount_price, 2, ',', ' ') }} EUR)
                                </span>
                                @endif
                            </p>

                            <!-- Отзывы -->
                            <div class="reviews mt-2">
                                <div class="star-rating">
                                    @php
                                        $rating = round($product->rating ?? 0, 1); // NEW: Проверка rating
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
                                    <span class="rating-value">({{ number_format($rating, 1) }})</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Пагинация (сохранена как в текущей версии) -->
    <div class="d-flex justify-content-center mt-4">
        {{ $products->links() }}
    </div>
@endsection
