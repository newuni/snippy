<?php

use App\Http\Controllers\PasteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PasteController::class, 'index'])->name('pastes.index');
Route::get('/new', [PasteController::class, 'create'])->name('pastes.create');
Route::post('/new', [PasteController::class, 'store'])->name('pastes.store');
Route::get('/{paste}', [PasteController::class, 'show'])->name('pastes.show');
Route::get('/{paste}/raw', [PasteController::class, 'raw'])->name('pastes.raw');
