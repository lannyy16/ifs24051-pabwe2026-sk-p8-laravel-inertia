# Hasil Pemeriksaan Awal — BlogVerse Pro P8

Paket ini dibuat dari source ZIP yang diunggah, dengan perbaikan terbatas yang dapat diverifikasi secara statis.

## Perbaikan pada paket ini
- Menambahkan route publik `GET /posts` sebagai alias daftar artikel publik. Source ZIP yang diunggah belum memuat route ini.
- Menambahkan indikator Top Progress Bar pada layout utama, tanpa menghapus Floating Status Pill.
- Menghapus duplikasi deklarasi `@vitejs/plugin-react` pada `package.json` (dipertahankan sebagai devDependency).
- Menambahkan assertion akses `/posts` pada feature test publik.
- Tidak menyertakan `.env`, `.git`, `vendor`, `node_modules`, session runtime, log runtime, compiled view, atau cache hasil generate ke dalam ZIP.

## Pemeriksaan terhadap ketentuan tugas
| Ketentuan | Status pemeriksaan source |
|---|---|
| Laravel 13, Inertia.js, React, PostgreSQL | Dikonfigurasi pada `composer.json`, `package.json`, dan `.env.example` |
| Feature-First | Struktur `app/Features` untuk Admin, Ai, Auth, Post, dan Profile tersedia |
| Akses Publik, Member, Admin | Route, middleware role, dan policy tersedia |
| Model Post, relasi User, schema, enum kategori | Tersedia |
| CRUD, publikasi, cover image | Implementasi tersedia |
| Import/export Excel | Implementasi PhpSpreadsheet tersedia |
| Integrasi OpenAI | Service dan interface tersedia; API key harus diisi pada environment |
| Markdown editor/viewer | TOAST UI Editor dan Viewer tersedia |
| UX list, statistik, kebab menu, modal full-screen | Implementasi tersedia |
| Top Progress Bar dan Floating Status Pill | Keduanya tersedia setelah perbaikan layout |
| Storage `.gitignore` | Placeholder `.gitignore` untuk direktori storage tersedia |
| 100% code coverage pada `app/` | **Belum terbukti**. Source saat diunggah hanya memiliki 11 test method. Jenkinsfile menerapkan gate coverage 100%, sehingga test suite perlu diperluas dan diverifikasi di image CI yang memiliki PCOV/Xdebug |
| `composer audit` bersih | **Belum diverifikasi**. Jenkinsfile menjalankan `composer audit`; hasil final harus dilihat dari build Jenkins |

## Batas verifikasi
- Seluruh file PHP pada source berhasil melewati `php -l` (tidak ditemukan syntax error).
- PHPUnit belum dapat dijalankan pada environment pemeriksaan ini karena ekstensi PHP `dom`, `mbstring`, dan `xmlwriter` tidak tersedia.
- Frontend build belum dapat diverifikasi karena dependency native Rollup untuk Linux tidak tersedia pada `node_modules` dari ZIP. Jalankan `npm ci` lalu `npm run build` pada mesin pengembangan atau biarkan stage frontend Jenkins membangunnya.
- Jangan menganggap paket ini sudah lolos 100% coverage atau `composer audit` sebelum Jenkins menunjukkan hasil sukses.

## Instalasi lokal
1. Ekstrak ZIP.
2. Jalankan `composer install`.
3. Salin `.env.example` menjadi `.env`, lalu atur PostgreSQL sesuai ketentuan tugas.
4. Jalankan `php artisan key:generate`.
5. Jalankan `php artisan migrate --seed`.
6. Jalankan `php artisan storage:link`.
7. Jalankan `npm ci` dan `npm run build`.
8. Jalankan `php artisan serve`.

**Keamanan:** jangan memasukkan `.env` ke ZIP atau Git. Atur kredensial database dan `OPENAI_API_KEY` melalui environment masing-masing.
