<?php

namespace App\Repositories;

use App\Models\SuperAdmin;
use App\Models\Merchant;


class SuperAdminRepository
{
    public function findByEmailAndRole($email, $role)
    {
        return SuperAdmin::where('email', $email)->where('role', $role)->first();
    }

    public function create(array $data)
    {
        return SuperAdmin::create($data);
    }

    public function findById($id)
    {
        return SuperAdmin::find($id);
    }

    public function update(array $data, $id)
    {
        return SuperAdmin::where('id', $id)->update($data);
    }

 public function deleteMerchant($merchantId)
{
    // Use Eloquent to delete the merchant
    $merchant = Merchant::find($merchantId);

    if (!$merchant) {
        \Log::info("Merchant not found with ID: {$merchantId}");
        return false; // Or handle as required
    }

    return $merchant->delete();
}

}
