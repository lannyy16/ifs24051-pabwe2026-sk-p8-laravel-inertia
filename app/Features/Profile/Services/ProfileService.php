<?php

namespace App\Features\Profile\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProfileService
{
    /** @param array{name: string, username: string, email: string} $data */
    public function updateProfile(User $user, array $data): bool
    {
        return $user->update([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
        ]);
    }

    public function updatePassword(User $user, string $newPassword): bool
    {
        return $user->update(['password' => Hash::make($newPassword)]);
    }

    /**
     * Potong gambar menjadi persegi 1:1 (400px) lalu simpan sebagai foto profil.
     *
     * @param  string  $imageData  path berkas lokal, data URI base64, atau isi biner gambar
     */
    public function updateProfilePhoto(User $user, string $imageData): string
    {
        if (str_contains($imageData, ';base64,')) {
            [, $content] = explode(';base64,', $imageData);
            $binary = base64_decode($content);
        } elseif (is_file($imageData)) {
            $binary = file_get_contents($imageData);
        } else {
            $binary = $imageData;
        }

        $source = @imagecreatefromstring($binary);
        if (! $source) {
            throw new RuntimeException('Format gambar tidak valid atau tidak didukung.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $crop = min($width, $height);
        $target = 400;

        $canvas = imagecreatetruecolor($target, $target);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $source, 0, 0, (int) (($width - $crop) / 2), (int) (($height - $crop) / 2), $target, $target, $crop, $crop);

        ob_start();
        imagepng($canvas, null, 8);
        $png = ob_get_clean();

        $this->deleteProfilePhoto($user);

        $filename = 'profile-photos/'.Str::random(40).'.png';
        Storage::disk('public')->put($filename, $png);
        $user->update(['profile_photo_path' => $filename]);

        return $filename;
    }

    public function deleteProfilePhoto(User $user): bool
    {
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        return $user->update(['profile_photo_path' => null]);
    }
}
