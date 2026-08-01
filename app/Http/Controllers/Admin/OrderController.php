<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Requests\Order\UpdateShippingCostRequest;
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
        $orders = $this->orderService->listAll($request->query('status'));

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

    public function show(int $orderId)
    {
        $order = $this->orderService->findWithDetails($orderId);

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب تفاصيل الطلب بنجاح',
            'data' => new OrderResource($order),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $orderId)
    {
        $order = $this->orderService->findWithDetails($orderId);

        $order = $this->orderService->updateStatus(
            $order,
            $request->validated()['status'],
            $request->validated()['note'] ?? null,
            $request->user()->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'data' => new OrderResource($order),
        ]);
    }

    public function updateShippingCost(UpdateShippingCostRequest $request, int $orderId)
    {
        $order = $this->orderService->findWithDetails($orderId);
        $order = $this->orderService->updateShippingCost(
            $order,
            (float) $request->validated()['shipping_cost']
        );
        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث سعر الشحن بنجاح',
            'data' => new OrderResource($order),
        ]);
    }
}