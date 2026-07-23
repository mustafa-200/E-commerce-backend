<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Models\Category;

class StatsController extends Controller
{
    public function index()
    {
        $revenue = Order::where('order_status', OrderStatus::Delivered->value)
            ->sum('total');

        $ordersCount = Order::count();

        $productsCount = Product::count();
        $CategoriesCount = Category::count();

        // العملاء بس (استثناء الأدمن)
        $customersCount = User::where('role', '!=', 'admin')->count();

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب الإحصائيات بنجاح',
            'data' => [
                'products_count' => $productsCount,
                'orders_count' => $ordersCount,
                'revenue' => (float) $revenue,
                'customers_count' => $customersCount,
                'categories_count' => $CategoriesCount,
            ],
        ]);
    }
}
