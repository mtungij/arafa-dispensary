<?php

use App\Actions\Auth\Logout;
use App\Livewire;
use App\Livewire\Auth\ConfirmPassword;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Dashboard;
use App\Livewire\Settings\Account;
use App\Livewire\Transactions;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Livewire\Home::class)->name('home');

/** AUTH ROUTES */
Route::livewire('/register', Register::class)->name('register');

Route::livewire('/login', Login::class)->name('login');

Route::livewire('/forgot-password', ForgotPassword::class)->name('forgot-password');

Route::livewire('reset-password/{token}', ResetPassword::class)->name('password.reset');

Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', Dashboard::class)->name('dashboard');
    Route::livewire('/transactions', Transactions::class)->name('transactions');
    Route::livewire('/settings/account', Account::class)->name('settings.account');
});

Route::livewire('/post/create', 'pages::post.create');
Route::middleware('auth')->group(function () {
     Route::livewire('/employee/index', 'pages::employee.index')->name('employee.index');
//     Route::livewire('/employee/create', 'pages::employee.create')->name('employee.create');
//     Route::livewire('/employee/{id}/edit', 'pages::employee.edit')->name('employee.edit');
//     Route::livewire('/employee/{id}', 'pages::employee.show')->name('employee.show');
});
Route::middleware('auth')->group(function () {
     Route::livewire('/reception/index', 'pages::reception.visit')->name('reception.index');
     Route::livewire('/reception/create-patient', 'pages::reception.create-patient')->name('reception.create-patient');

});

Route::middleware('auth')->group(function () {
    Route::livewire('/settings/registration-fees', 'pages::settings.registeration-fee')->name('settings.registration-fees');
    Route::livewire('/settings/investigationMaster', 'pages::settings.investigation-manager')->name('settings.investigation-master');
});


Route::middleware('auth')->group(function () {
    Route::livewire('doctor/dashboard', 'pages::doctor.dashboard')->name('doctor.dashboard');
    Route::livewire('doctor/consultation/{visitId}','pages::doctor.patient-consultation')->name('doctor.consultation');
});


Route::middleware('auth')->group(function () {
    Route::livewire('billing/index', 'pages::billing.dashboard')->name('billing.index');
});

Route::middleware(['auth'])->group(function () {
    Route::livewire('/auth/verify-email', VerifyEmail::class)
        ->name('verification.notice');
    Route::post('/logout', Logout::class)
        ->name('app.auth.logout');
    Route::livewire('confirm-password', ConfirmPassword::class)
        ->name('password.confirm');
});

Route::middleware(['auth', 'signed'])->group(function () {
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect(route('home'));
    })->name('verification.verify');
});
