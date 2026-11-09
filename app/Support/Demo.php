<?php

namespace App\Support;

use App\Models\User;

class Demo
{
    public static function worker(): User
    {
        $user = auth()->user();
        abort_unless($user && $user->role === 'worker', 403);

        return $user;
    }
}
