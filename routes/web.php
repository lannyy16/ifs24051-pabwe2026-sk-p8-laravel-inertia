
<?php

use App\Features\Admin\Controllers\AdminUserController;
use App\Features\Auth\Controllers\AuthController;
use App\Features\Post\Controllers\AdminPostController;
use App\Features\Post\Controllers\PostController;
use App\Features\Post\Controllers\PublicPostController;
use App\Features\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:1000-per-second'])->group(function () {

    // ==================================================
    // TINGKAT 1: HALAMAN PUBLIK (TANPA LOGIN)
    // ==================================================

    Route::get('/', [PublicPostController::class, 'index'])
        ->name('home');

    Route::get('/posts', [PublicPostController::class, 'index'])
        ->name('public.posts.index');

    Route::get('/articles', [PublicPostController::class, 'index'])
        ->name('articles.index');

    Route::get('/articles/{slug}', [PublicPostController::class, 'show'])
        ->name('articles.show');


    // ==================================================
    // AUTENTIKASI: LOGIN DAN REGISTER (GUEST)
    // ==================================================

    Route::middleware('guest')->group(function () {

        Route::get('/login', [AuthController::class, 'createLogin'])
            ->name('login');

        Route::post('/login', [AuthController::class, 'storeLogin']);

        Route::get('/register', [AuthController::class, 'createRegister'])
            ->name('register');

        Route::post('/register', [AuthController::class, 'storeRegister']);
    });


    // ==================================================
    // TINGKAT 2 DAN 3: HALAMAN PENGGUNA LOGIN
    // ==================================================

    Route::middleware('auth')->group(function () {

        // Logout
        Route::post('/logout', [AuthController::class, 'destroy'])
            ->name('logout');


        // ----------------------------------------------
        // PROFIL PENGGUNA
        // ----------------------------------------------

        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('profile');

        Route::patch('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
            ->name('profile.password');

        Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])
            ->name('profile.photo.update');

        Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])
            ->name('profile.photo.delete');


        // ----------------------------------------------
        // DASHBOARD PENULIS
        // ----------------------------------------------

        Route::redirect('/dashboard', '/dashboard/posts');

        Route::prefix('dashboard/posts')
            ->name('posts.')
            ->group(function () {

                // Ekspor dan impor Excel
                Route::get('/export', [PostController::class, 'export'])
                    ->name('export');

                Route::post('/import', [PostController::class, 'import'])
                    ->name('import');


                // Fitur AI
                Route::post('/ai-generate', [PostController::class, 'aiGenerate'])
                    ->name('ai.generate');

                Route::post('/ai-save', [PostController::class, 'aiSave'])
                    ->name('ai.save');


                // Daftar artikel penulis
                Route::get('/', [PostController::class, 'index'])
                    ->middleware('owns.post')
                    ->name('index');

                // Membuat artikel
                Route::post('/', [PostController::class, 'store'])
                    ->name('store');


                // Operasi berdasarkan ID artikel
                Route::middleware('owns.post')
                    ->whereNumber('post')
                    ->group(function () {

                        Route::get('/{post}', [PostController::class, 'show'])
                            ->name('show');

                        Route::put('/{post}', [PostController::class, 'update'])
                            ->name('update');

                        Route::delete('/{post}', [PostController::class, 'destroy'])
                            ->name('destroy');

                        Route::patch('/{post}/publish', [PostController::class, 'publish'])
                            ->name('publish');

                        Route::post('/{post}/cover', [PostController::class, 'cover'])
                            ->name('cover.update');

                        Route::delete('/{post}/cover', [PostController::class, 'destroyCover'])
                            ->name('cover.delete');
                    });
            });


        // ----------------------------------------------
        // TINGKAT 3: ADMINISTRATOR
        // ----------------------------------------------

        Route::prefix('admin')
            ->name('admin.')
            ->middleware('role:admin')
            ->group(function () {

                // Manajemen pengguna
                Route::get('/users', [AdminUserController::class, 'index'])
                    ->name('users');

                // Artikel berdasarkan pengguna
                Route::get('/users/{user}/posts', [AdminPostController::class, 'userPosts'])
                    ->name('users.posts');

                Route::get('/users/{user}/posts/export', [AdminPostController::class, 'exportUser'])
                    ->name('users.posts.export');


                // Manajemen seluruh artikel
                Route::get('/posts', [AdminPostController::class, 'index'])
                    ->name('posts');

                Route::get('/posts/export', [AdminPostController::class, 'export'])
                    ->name('posts.export');
            });
    });
});
