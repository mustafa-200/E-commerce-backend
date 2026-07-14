<?php

namespace App\Services\Address;

use App\Models\Address;
use Illuminate\Support\Collection;

class AddressService
{
    public function list(int $userId): Collection
    {
        return Address::where('user_id', $userId)->latest()->get();
    }

    public function create(int $userId, array $data): Address
    {
        if (!empty($data['is_default'])) {
            Address::where('user_id', $userId)->update(['is_default' => false]);
        }

        $data['user_id'] = $userId;

        return Address::create($data);
    }

    public function update(Address $address, array $data): Address
    {
        if (!empty($data['is_default'])) {
            Address::where('user_id', $address->user_id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($data);

        return $address;
    }

    public function delete(Address $address): void
    {
        $address->delete();
    }
}
