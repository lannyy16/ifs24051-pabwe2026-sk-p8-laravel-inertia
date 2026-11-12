<?php
namespace App\Features\Auth\Actions;
use App\Models\User;
class RegisterUserAction { public function execute(array $data):User{return User::create([...$data,'role'=>'member']);} }
