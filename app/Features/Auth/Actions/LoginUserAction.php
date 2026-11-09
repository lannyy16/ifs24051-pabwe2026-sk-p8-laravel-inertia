<?php
namespace App\Features\Auth\Actions;
use Illuminate\Support\Facades\Auth; use Illuminate\Validation\ValidationException;
class LoginUserAction { public function execute(array $credentials,bool $remember=false):void{if(!Auth::attempt($credentials,$remember))throw ValidationException::withMessages(['username'=>'Email atau password tidak sesuai.']);request()->session()->regenerate();} }
