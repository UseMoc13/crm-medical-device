<?php

use App\Http\Controllers\WorkspacePersonalNoteController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureCrmAuthenticated::class])->group(function (): void {
    Route::patch('/notes/{note}/pin', [WorkspacePersonalNoteController::class, 'togglePin'])->whereUuid('note')->name('notes.pin');
    Route::patch('/notes/{note}/archive', [WorkspacePersonalNoteController::class, 'archive'])->whereUuid('note')->name('notes.archive');
    Route::patch('/notes/{note}/restore', [WorkspacePersonalNoteController::class, 'restore'])->whereUuid('note')->name('notes.restore');
    Route::resource('notes', WorkspacePersonalNoteController::class)->parameters(['notes' => 'note']);
});
