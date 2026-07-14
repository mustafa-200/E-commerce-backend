<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\Address\AddressResource;
use App\Models\Address;
use App\Services\Address\AddressService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(protected AddressService $addressService)
    {
    }

    public function index(Request $request)
    {
        $addresses = $this->addressService->list($request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب العناوين بنجاح',
            'data' => AddressResource::collection($addresses),
        ]);
    }

    public function store(StoreAddressRequest $request)
    {
        $address = $this->addressService->create($request->user()->id, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة العنوان بنجاح',
            'data' => new AddressResource($address),
        ], 201);
    }

    public function update(UpdateAddressRequest $request, Address $address)
    {
        // تأكيد أمني: العنوان ده بتاع نفس المستخدم؟
        abort_if($address->user_id !== $request->user()->id, 403, 'غير مصرح لك بتعديل هذا العنوان.');

        $address = $this->addressService->update($address, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث العنوان بنجاح',
            'data' => new AddressResource($address),
        ]);
    }

    public function destroy(Request $request, Address $address)
    {
        abort_if($address->user_id !== $request->user()->id, 403, 'غير مصرح لك بحذف هذا العنوان.');

        $this->addressService->delete($address);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف العنوان بنجاح',
        ]);
    }
}
