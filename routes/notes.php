<?php

use App\Http\Controllers\WorkspacePersonalNoteController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureCrmAuthenticated::class])->group(function (): void {
    // Global floating Notes API. Explicit routes precede resource wildcard.
    Route::get('/notes/floating/data', [\App\Http\Controllers\WorkspaceFloatingNotesController::class, 'index'])->name('notes.floating.index');
    Route::post('/notes/floating/data', [\App\Http\Controllers\WorkspaceFloatingNotesController::class, 'create'])->middleware('throttle:20,1')->name('notes.floating.create');
    Route::get('/notes/floating/data/{note}', [\App\Http\Controllers\WorkspaceFloatingNotesController::class, 'show'])->whereUuid('note')->name('notes.floating.show');
    Route::patch('/notes/floating/data/{note}', [\App\Http\Controllers\WorkspaceFloatingNotesController::class, 'save'])->whereUuid('note')->middleware('throttle:120,1')->name('notes.floating.save');
    Route::delete('/notes/floating/data/{note}', [\App\Http\Controllers\WorkspaceFloatingNotesController::class, 'destroy'])->whereUuid('note')->name('notes.floating.destroy');
    Route::get('/notes/mention-options', [\App\Http\Controllers\WorkspaceFloatingNotesController::class, 'search'])->middleware('throttle:90,1')->name('notes.mention-options');
    Route::patch('/notes/{note}/pin', [WorkspacePersonalNoteController::class, 'togglePin'])->whereUuid('note')->name('notes.pin');
    Route::patch('/notes/{note}/archive', [WorkspacePersonalNoteController::class, 'archive'])->whereUuid('note')->name('notes.archive');
    Route::patch('/notes/{note}/restore', [WorkspacePersonalNoteController::class, 'restore'])->whereUuid('note')->name('notes.restore');
    Route::resource('notes', WorkspacePersonalNoteController::class)->parameters(['notes' => 'note']);
});
