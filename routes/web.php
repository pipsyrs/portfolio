<?php

use App\Http\Controllers\CvController;
use App\Http\Controllers\SecureFileController;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('index'))
    ->middleware('track.visitor')
    ->name('index');

Route::get('/view/cv', [CvController::class, 'show'])->name('view.cv');

Route::get('lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'id'], true), 404);

    session()->put('locale', $locale);

    return back();
})->name('lang.switch');

/*
|--------------------------------------------------------------------------
| Dashboard (single-account)
|--------------------------------------------------------------------------
*/

Route::prefix('pipspanel')->name('dashboard.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', Dashboard\Auth\Login::class)->name('login');
    });

    Route::middleware(['auth', 'auth.session', 'session.absolute', 'owner'])->group(function () {
        Route::get('/', Dashboard\Home::class)->name('home');

        Route::get('projects', Dashboard\Projects\Index::class)->name('projects');
        Route::get('projects/create', Dashboard\Projects\Form::class)->name('projects.create');
        Route::get('projects/{project}/edit', Dashboard\Projects\Form::class)->name('projects.edit');

        Route::get('tech-stacks', Dashboard\TechStacks\Index::class)->name('tech-stacks');
        Route::get('specializations', Dashboard\Specializations\Index::class)->name('specializations');
        Route::get('contacts', Dashboard\Contacts\Index::class)->name('contacts');
        Route::get('notifications', Dashboard\Notifications\Index::class)->name('notifications');
        Route::get('profile', Dashboard\Profile\Edit::class)->name('profile');
        Route::get('settings', Dashboard\Settings\Edit::class)->name('settings');
        Route::get('backup', Dashboard\Backup\Index::class)->name('backup');

        // Signed URL berumur pendek: tautan yang bocor tidak bisa dipakai ulang.
        Route::get('backup/download/{file}', [SecureFileController::class, 'backup'])
            ->name('backup.download')
            ->middleware('signed');

        Route::post('logout', function () {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('dashboard.login');
        })->name('logout');
    });
});
