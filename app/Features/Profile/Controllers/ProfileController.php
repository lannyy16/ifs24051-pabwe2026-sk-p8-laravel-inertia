<?php

namespace App\Features\Profile\Controllers;

use App\Features\Profile\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function __construct(private readonly ProfileService $profile) {}

    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Profile/Edit', [
            'user' => $user->only(['id', 'name', 'username', 'email', 'role', 'profile_photo_path', 'profile_photo_url']),
            'postCount' => $user->posts()->count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $this->profile->updateProfile($user, $validated);

        return back()->with('success', 'Identitas profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini yang Anda masukkan salah.',
            ]);
        }

        $this->profile->updatePassword($user, $validated['password']);

        return back()->with('success', 'Password akun berhasil diubah.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate(['photo' => ['required', 'image', 'max:2048']]);

        $this->profile->updateProfilePhoto($request->user(), $request->file('photo')->getRealPath());

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function deletePhoto(Request $request): RedirectResponse
    {
        $this->profile->deleteProfilePhoto($request->user());

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }
}
