<?php

namespace App\Services\User;

use App\Data\User\UserCreateData;
use App\Data\User\UserUpdateData;
use App\Models\User;

class UserService
{
    public function create(UserCreateData $data): User
    {
        $user = new User();
        $user->name = $data->name;
        $user->email = $data->email;
        $user->password = bcrypt($data->password);
        $user->email_verified_at = $data->email_verified_at;

        $user->save();

        return $user;
    }

    public function update(UserUpdateData $data): User
    {
        $user = $data->user;

        if ($data->name && $user->name !== $data->name) $user->name = $data->name;
        if ($data->email && $user->email !== $data->email) $user->email = $data->email;
        if ($data->password) $user->password = bcrypt($data->password);
        if ($data->email_verified_at) $user->email_verified_at = $data->email_verified_at;

        if ($user->isDirty()) $user->save();

        return $user;
    }
}
