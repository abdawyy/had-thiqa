<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientOrderController;
use App\Http\Controllers\ExecutionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\WorkerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/payout', [PayoutController::class, 'store'])->middleware('auth')->name('payout.store');

Route::prefix('worker')->name('worker.')->middleware(['auth', 'role:worker'])->group(function () {
    Route::get('/', [WorkerController::class, 'home'])->name('home');
    Route::post('/availability', [WorkerController::class, 'availability'])->name('availability');
    Route::post('/help', [WorkerController::class, 'help'])->name('help');
    Route::get('/skills', [WorkerController::class, 'skills'])->name('skills');
    Route::post('/skills', [WorkerController::class, 'storeSkill'])->name('skills.store');
    Route::get('/projects', [WorkerController::class, 'projects'])->name('projects');
    Route::get('/account', [WorkerController::class, 'account'])->name('account');
    Route::get('/analytics', [WorkerController::class, 'analytics'])->name('analytics');
    Route::post('/offers/{offer}/claim', [ExecutionController::class, 'claim'])->name('claim');
    Route::get('/orders/{order}', [ExecutionController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/begin', [ExecutionController::class, 'begin'])->name('orders.begin');
    Route::post('/orders/{order}/steps/{step}', [ExecutionController::class, 'step'])->name('orders.step');
    Route::post('/orders/{order}/steps/{step}/skill', [ExecutionController::class, 'skill'])->name('orders.skill');
    Route::post('/orders/{order}/steps/{step}/confirm', [ExecutionController::class, 'confirmSkill'])->name('orders.skill.confirm');
    Route::post('/orders/{order}/deliver', [ExecutionController::class, 'deliver'])->name('orders.deliver');
    Route::post('/orders/{order}/approve', [ExecutionController::class, 'approve'])->name('orders.approve');
    Route::get('/orders/{order}/split', [ExecutionController::class, 'split'])->name('orders.split');
});

Route::prefix('client')->name('client.')->group(function () {
    Route::get('/request', [ClientOrderController::class, 'create'])->name('request');
    Route::post('/request', [ClientOrderController::class, 'store'])->name('store');
    Route::get('/orders/{order}', [ClientOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/pay', [ClientOrderController::class, 'pay'])->name('orders.pay');
    Route::post('/orders/{order}/simulate', [ClientOrderController::class, 'simulate'])->name('orders.simulate');
    Route::post('/orders/{order}/rate', [ClientOrderController::class, 'rate'])->name('orders.rate');
});

Route::any('/seller/{any?}', fn () => redirect()->route('home'))->where('any', '.*')->name('seller.hidden');

Route::prefix('seller')->name('seller.')->middleware(['auth', 'role:seller'])->group(function () {
    Route::get('/', [SellerController::class, 'home'])->name('home');
    Route::get('/analytics', [SellerController::class, 'analytics'])->name('analytics');
    Route::post('/gigs', [SellerController::class, 'store'])->name('gigs.store');
    Route::get('/gigs/{service}', [SellerController::class, 'show'])->name('gigs.show');
    Route::put('/gigs/{service}', [SellerController::class, 'update'])->name('gigs.edit');
    Route::patch('/gigs/{service}', [SellerController::class, 'update'])->name('gigs.update');
    Route::post('/gigs/{service}/duplicate', [SellerController::class, 'duplicate'])->name('gigs.duplicate');
    Route::delete('/gigs/{service}', [SellerController::class, 'destroy'])->name('gigs.destroy');
    Route::get('/orders/{order}', [SellerController::class, 'order'])->name('orders.show');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('home');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::post('/services', [AdminController::class, 'store'])->name('services.store');
    Route::patch('/services/{service}', [AdminController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [AdminController::class, 'destroy'])->name('services.destroy');
    Route::post('/skills/{skill}', [AdminController::class, 'reviewSkill'])->name('skills.review');
    Route::post('/tickets/{ticket}/close', [AdminController::class, 'closeTicket'])->name('tickets.close');
});
