<?php

use App\Http\Controllers\TodoController;
use App\Http\Controllers\WorkspaceTaskSubmissionController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureCrmAuthenticated::class])->group(function () {
    Route::patch('todos/{todo}/complete', [TodoController::class, 'complete'])->name('todos.complete');
    // Specific submission / archive routes must come before the resource show route.
    Route::post('todos/{todo}/submission/files', [WorkspaceTaskSubmissionController::class, 'upload'])->name('todos.submission.upload');
    Route::patch('todos/{todo}/submission/complete', [WorkspaceTaskSubmissionController::class, 'complete'])->name('todos.submission.complete');
    Route::delete('todos/{todo}/submission/files/{file}', [WorkspaceTaskSubmissionController::class, 'removeFile'])->name('todos.submission.files.destroy');
    Route::get('todos/{todo}/submission/files/{file}/download', [WorkspaceTaskSubmissionController::class, 'downloadFile'])->name('todos.submission.files.download');
    Route::get('todos/{todo}/assignments/{assignment}/download', [WorkspaceTaskSubmissionController::class, 'downloadAssignment'])->name('todos.assignments.download');
    Route::get('todos/{todo}/download-all', [WorkspaceTaskSubmissionController::class, 'downloadAll'])->name('todos.submissions.download-all');
    Route::resource('todos', TodoController::class)->parameters(['todos' => 'todo']);
});
