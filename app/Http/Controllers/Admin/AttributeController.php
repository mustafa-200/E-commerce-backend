<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attribute\StoreAttributeRequest;
use App\Http\Requests\Attribute\UpdateAttributeRequest;
use App\Http\Requests\Attribute\StoreAttributeValueRequest;
use App\Http\Resources\Attribute\AttributeResource;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Services\Attribute\AttributeService;

class AttributeController extends Controller
{
    public function __construct(protected AttributeService $attributeService)
    {
    }

    public function index()
    {
        $attributes = $this->attributeService->list();

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب الخصائص بنجاح',
            'data' => AttributeResource::collection($attributes),
        ]);
    }

    public function store(StoreAttributeRequest $request)
    {
        $attribute = $this->attributeService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء الخاصية بنجاح',
            'data' => new AttributeResource($attribute),
        ], 201);
    }

    public function show(Attribute $attribute)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب الخاصية بنجاح',
            'data' => new AttributeResource($attribute->load('values')),
        ]);
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute)
    {
        $attribute = $this->attributeService->update($attribute, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الخاصية بنجاح',
            'data' => new AttributeResource($attribute),
        ]);
    }

    public function destroy(Attribute $attribute)
    {
        $this->attributeService->delete($attribute);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الخاصية بنجاح',
        ]);
    }

    // إضافة قيمة جديدة لخاصية موجودة (مثلاً: لون جديد "أخضر" لـ Attribute "Color")
    public function storeValue(StoreAttributeValueRequest $request, Attribute $attribute)
    {
        $value = $this->attributeService->addValue($attribute, $request->validated()['value']);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة القيمة بنجاح',
            'data' => $value,
        ], 201);
    }

    // حذف قيمة معينة
    public function destroyValue(AttributeValue $attributeValue)
    {
        $this->attributeService->deleteValue($attributeValue);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف القيمة بنجاح',
        ]);
    }
}