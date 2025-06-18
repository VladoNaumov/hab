<!-- order/show.blade.php  -->

@extends('layouts.app')

@section('title', 'Order #' . htmlspecialchars($order->order_number)) <!-- NEW: Используем order_number вместо id и добавляем htmlspecialchars -->

@section('content')
    <div class="container my-5">
        <!-- Улучшенная обработка ошибок и сообщений -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">Error</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @elseif (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <h1 class="text-center mb-4">Order #{{ htmlspecialchars($order->order_number) }}</h1> <!-- NEW: Используем order_number вместо id -->

        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Order details card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Order Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Order Number:</strong> {{ htmlspecialchars($order->order_number) }}</p> <!-- NEW: Добавляем htmlspecialchars -->
                                <p><strong>Status:</strong> <span
                                        class="badge bg-{{ $order->status === 'pending' ? 'warning' : 'success' }}">{{ htmlspecialchars($order->status) }}</span>
                                </p> <!-- NEW: Добавляем htmlspecialchars -->
                                <p><strong>Total:</strong> {{ number_format($order->total, 2, '.', ' ') }} EUR</p>
                                <p><strong>Date:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Customer:</strong> {{ htmlspecialchars($order->first_name . ' ' . $order->last_name) }}</p> <!-- NEW: Добавляем htmlspecialchars -->
                                <p><strong>Email:</strong> {{ htmlspecialchars($order->email) }}</p> <!-- NEW: Добавляем htmlspecialchars -->
                                <p><strong>Address:</strong> {{ htmlspecialchars($order->city . ', ' . $order->postal_code . ', ' . $order->address) }}</p> <!-- NEW: Добавляем htmlspecialchars -->
                                <p><strong>Phone:</strong> {{ htmlspecialchars($order->phone ?? 'N/A') }}</p> <!-- NEW: Добавляем htmlspecialchars -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Products</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                <tr>
                                    <th>Article</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($order->orderItems as $item)
                                    <tr>
                                        <td>{{ htmlspecialchars($item->product->article) }}</td>
                                        <td>{{ htmlspecialchars($item->product->name) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->discount_price ?? $item->price, 2, '.', ' ') }} EUR</td>
                                        <td>{{ number_format($item->subtotal, 2, '.', ' ') }} EUR</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No products</td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot>
                                <tr class="table-dark">
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td>{{ number_format($order->total, 2, '.', ' ') }} EUR</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Return button -->
                <div class="text-center mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
@endsection
