<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderResource;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
    }

    public function index(Request $request)
    {
        $orders = $this->orderService->listForUser($request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب الطلبات بنجاح',
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, int $orderId)
    {
        $order = $this->orderService->findForUser($orderId, $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب تفاصيل الطلب بنجاح',
            'data' => new OrderResource($order),
        ]);
    }
}
