<?php

namespace App\Repositories;

use App\Models\SuperAdmin;

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
}
