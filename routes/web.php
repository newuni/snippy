<?php

use App\Http\Controllers\PasteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PasteController::class, 'index'])->name('pastes.index');
Route::get('/explore', [PasteController::class, 'explore'])->name('pastes.explore');
Route::get('/new', [PasteController::class, 'create'])->name('pastes.create');
Route::get('/manage/{paste:manage_token}', [PasteController::class, 'manage'])->name('pastes.manage');
Route::put('/manage/{paste:manage_token}/autosave', [PasteController::class, 'autosave'])->name('pastes.autosave');
Route::post('/manage/{paste:manage_token}/publish', [PasteController::class, 'publish'])->name('pastes.publish');
Route::post('/manage/{paste:manage_token}/unpublish', [PasteController::class, 'unpublish'])->name('pastes.unpublish');
Route::delete('/manage/{paste:manage_token}/password', [PasteController::class, 'clearPassword'])->name('pastes.password.clear');
Route::get('/manage/{paste:manage_token}/raw', [PasteController::class, 'draftRaw'])->name('pastes.draft.raw');
Route::get('/p/{paste:slug}', [PasteController::class, 'show'])->name('pastes.show');
Route::post('/p/{paste:slug}/unlock', [PasteController::class, 'unlock'])->name('pastes.unlock');
Route::get('/p/{paste:slug}/raw', [PasteController::class, 'raw'])->name('pastes.raw');
