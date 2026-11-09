<?php
namespace App\Features\Profile\Controllers;
use App\Http\Controllers\Controller;use Illuminate\Http\Request;use Inertia\Inertia;use App\Features\Profile\Services\ProfileService;
class ProfileController extends Controller { public function edit(Request $r){return Inertia::render('Profile/Edit',['user'=>$r->user()->only('name','email')]);} public function update(Request $r,ProfileService $s){$data=$r->validate(['name'=>'required|string|max:120','email'=>'required|email|max:255|unique:users,email,'.$r->user()->id]);$s->update($r->user(),$data);return back()->with('success','Profil diperbarui.');} }
