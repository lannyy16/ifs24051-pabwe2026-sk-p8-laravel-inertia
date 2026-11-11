<?php

namespace App\Features\Auth\Actions;

use Illuminate\Support\Facades\Auth;

class LoginUserAction
{
    /**
     * Autentikasi memakai username ATAU email, lalu regenerasi sesi.
     */
    public function execute(string $login, string $password, bool $remember = false): bool
    {
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$field => $login, 'password' => $password], $remember)) {
            session()->regenerate();

            return true;
        }

        return false;
    }
}
