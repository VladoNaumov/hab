<!-- resources/views/layouts/app.blade.php -->

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MyBrand') }} - @yield('title')</title>

    <!-- Подключение стилей -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <!-- Подключение локальных стилей -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/alerts.css') }}" rel="stylesheet">
</head>
<body>
<!-- Навигационная панель -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="{{ route('products.index') }}">{{ config('app.name', 'MyBrand') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-4">
                    <div class="input-group search-group">
                            <span class="input-group-text">
                                <button id="search-btn" class="btn-link text-white p-0">
                                    <i class="bi bi-search"></i>
                                </button>
                            </span>
                        <input id="search-input" type="text" class="form-control d-none" style="width: 15rem;" placeholder="Search...">
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('products.index') ? 'active' : '' }} ms-3" id="products-link"
                       href="{{ route('products.index') }}">Products</a>
                </li>
                <li class="nav-item ms-3">
                    <a class="nav-link {{ Route::is('cart.index') ? 'active' : '' }} cart-icon"
                       href="{{ route('cart.index') }}">
                        <i class="bi bi-cart"></i>
                        @if (session('cart') && count(session('cart')) > 0)
                            <span class="badge bg-secondary cart-count-badge">
                                    {{ array_sum(array_column(session('cart'), 'quantity')) }}
                                </span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="main-content">
    <!-- Контент -->
    <div class="container mt-4">
        @if (session('success'))
            <div id="flash-message" class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div id="flash-message" class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<!-- Футер -->
<footer class="footer-section">
    <div class="container">
        <div class="row gy-4">
            <div class="col-md-4">
                <h5 class="footer-title">About Us</h5>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.</p>
            </div>
            <div class="col-md-4">
                <h5 class="footer-title">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="{{ route('products.index') }}">Home</a></li>
                    <li><a href="{{ route('cart.index') }}">Cart</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5 class="footer-title">Follow Us</h5>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="text-center copyright">
            © {{ date('Y') }} {{ config('app.name', 'MyBrand') }}. All rights reserved.
        </div>
    </div>
</footer>

<!-- Скрипты -->
<script>
    window.routes = {
        productsIndex: '{{ route('products.index') }}',
        cartUpdate: '{{ route('cart.update', ':id') }}',
        cartRemove: '{{ route('cart.remove', ':id') }}'
    };

    // Переключение поиска
    document.getElementById('search-btn').addEventListener('click', () => {
        const searchInput = document.getElementById('search-input');
        const productsLink = document.getElementById('products-link');
        searchInput.classList.toggle('d-none');
        if (!searchInput.classList.contains('d-none')) {
            productsLink.classList.add('ms-6', 'search-active');
            searchInput.focus();
        } else {
            productsLink.classList.remove('ms-6', 'search-active');
        }
    });

    // Автоматическое скрытие
    document.querySelectorAll('#flash-message').forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 1500);
    });
</script>
</body>
</html>
