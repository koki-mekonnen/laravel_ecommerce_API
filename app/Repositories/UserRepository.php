<?php
namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function findByEmailAndRole($email, $role)
    {
        return User::where('email', $email)->where('role', $role)->first();
    }

    public function findByPhoneAndRole($phone, $role)
    {
        return User::where('phone', $phone)
            ->where('role', $role)
            ->first();
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function findById($id)
    {
        return User::find($id);
    }

  public function update(array $data, $id)
{
    $user = User::findOrFail($id);
    $user->update($data);
    return $user;
}

}
