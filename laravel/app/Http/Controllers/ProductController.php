<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Отображает список продуктов
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Используем пагинацию (100 продуктов на страницу)
        $products = Product::paginate(100);

        //  Логируем количество продуктов
        Log::info('Loading products for index page', ['count' => $products->count(), 'total' => $products->total()]);

        return view('products.index', compact('products'));
    }

    /**
     * Отображает страницу продукта
     *
     * @param string $brand
     * @return \Illuminate\View\View
     */
    public function show(string $brand)
    {

        $product = Product::where('brand', $brand)->first();

        if (!$product) {
            Log::warning('Product not found', ['brand' => $brand, 'ip' => request()->ip()]);
            abort(404, 'Product not found for brand: ' . htmlspecialchars($brand));
        }

        $product->load('reviews');

        return view('products.show', compact('product'));
    }
}
