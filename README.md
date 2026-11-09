# BlogVerse Pro — P8 Laravel 13 + Inertia.js + React

Nama repositori/proyek: `ifs24051-pabwe2026-sk-p8-laravel-inertia`.

Aplikasi studi kasus P8 (Bagian C): platform blog multi-tier dengan Laravel 13, Inertia.js, React, PostgreSQL, PhpSpreadsheet, dan OpenAI.

## Kebutuhan
- PHP >= 8.4 dan ekstensi Laravel + PhpSpreadsheet (`gd`, `zip`, `mbstring`, `pdo_pgsql`, `pdo_sqlite`, `xml`, `xmlreader`, `xmlwriter`, `simplexml`, `iconv`, `zlib`, `bcmath`, `exif`).
- Composer, Node.js >= 20, npm, PostgreSQL >= 17.

## Instalasi (PowerShell)
```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
# Buat database PostgreSQL bernama pabwe_p8_blog terlebih dahulu
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun demo
- Admin: `admin@blogverse.test` / `password`
- Member: `member@blogverse.test` / `password`

Ganti password akun demo sebelum deployment.

## OpenAI
Isi `OPENAI_API_KEY` di `.env`. Tanpa API key, seluruh fitur blog tetap dapat digunakan; generator AI akan memberi pesan konfigurasi.

## Fitur utama
- Publik: beranda, pencarian, filter kategori, baca artikel Markdown, Login/Register.
- Member: CRUD artikel, status publikasi, cover image, import/export Excel, AI generator, profil.
- Admin: daftar pengguna, monitoring artikel pengguna dan semua artikel, export Excel.
- Pemisahan fitur di `app/Features`, middleware role, dan policy kepemilikan artikel.

## Jenkins
Jenkinsfile yang disediakan dosen tidak perlu diubah. `package-lock.json` sengaja dapat dihasilkan oleh `npm install` pada stage frontend bila belum tersedia. Folder `storage/framework/*` dan `storage/logs` menyimpan `.gitignore` agar direktori tetap ada saat checkout.

## Catatan verifikasi
Jalankan `composer audit`, `npm install`, `npm run build`, `php artisan test`, dan PHPUnit coverage di environment CI yang memiliki ekstensi `pcov` atau `xdebug`. Pipeline yang diberikan menetapkan gate coverage 100% pada `app/`; hasil gate tersebut harus diverifikasi pada image `delcom-ci-php:8.5`.
