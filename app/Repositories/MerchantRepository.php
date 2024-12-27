<?php

namespace App\Repositories;

use App\Models\Merchant;

class MerchantRepository
{
    public function findByEmailAndRole($email, $role)
    {
        return Merchant::where('email', $email)->where('role', $role)->first();
    }

    public function findByPhoneAndRole($phone, $role)
    {
        return Merchant::where('phone', $phone)
            ->where('role', $role)
            ->first();
    }

    public function create(array $data)
    {
        return Merchant::create($data);
    }

    public function findById($id)
    {
        return Merchant::find($id);
    }
}
