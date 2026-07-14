<?php

use App\Http\Controllers\PasteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PasteController::class, 'index'])->name('pastes.index');
Route::get('/explore', [PasteController::class, 'explore'])->name('pastes.explore');
Route::post('/new', [PasteController::class, 'create'])->middleware('throttle:draft-creation')->name('pastes.create');
Route::get('/llms.txt', [PasteController::class, 'agentGuide'])->name('agent.llms');
Route::get('/llms-full.txt', [PasteController::class, 'agentCorpus'])->middleware('throttle:public-aggregate')->name('agent.corpus');
Route::get('/agents.txt', [PasteController::class, 'agentsPolicy'])->name('agent.policy');
Route::get('/robots.txt', [PasteController::class, 'robots'])->name('agent.robots');
Route::get('/sitemap.xml', [PasteController::class, 'sitemap'])->middleware('throttle:public-aggregate')->name('agent.sitemap');
Route::get('/manage/{paste:manage_token}', [PasteController::class, 'manage'])->name('pastes.manage');
Route::put('/manage/{paste:manage_token}/autosave', [PasteController::class, 'autosave'])->middleware('throttle:managed-write')->name('pastes.autosave');
Route::post('/manage/{paste:manage_token}/publish', [PasteController::class, 'publish'])->middleware('throttle:managed-write')->name('pastes.publish');
Route::post('/manage/{paste:manage_token}/unpublish', [PasteController::class, 'unpublish'])->middleware('throttle:managed-write')->name('pastes.unpublish');
Route::delete('/manage/{paste:manage_token}/password', [PasteController::class, 'clearPassword'])->middleware('throttle:managed-write')->name('pastes.password.clear');
Route::get('/manage/{paste:manage_token}/raw', [PasteController::class, 'draftRaw'])->name('pastes.draft.raw');
Route::get('/p/{paste:slug}', [PasteController::class, 'show'])->name('pastes.show');
Route::post('/p/{paste:slug}/unlock', [PasteController::class, 'unlock'])->middleware('throttle:paste-unlock')->name('pastes.unlock');
Route::get('/p/{paste:slug}/raw', [PasteController::class, 'raw'])->name('pastes.raw');
