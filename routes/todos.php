<?php

use App\Http\Controllers\TodoController;
use App\Http\Controllers\WorkspaceCollaborationController;
use App\Http\Controllers\WorkspaceTaskSubmissionController;
use App\Http\Middleware\EnsureCrmAuthenticated;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureCrmAuthenticated::class])->group(function () {
    Route::get('todos/shared', [WorkspaceCollaborationController::class, 'sharedTodos'])->name('todos.shared');
    Route::get('todos/recipient-options', [TodoController::class, 'recipientOptions'])->name('todos.recipient-options');
    Route::patch('todos/{todo}/complete', [TodoController::class, 'complete'])->name('todos.complete');
    Route::post('todos/{todo}/submission/files', [WorkspaceTaskSubmissionController::class, 'upload'])->name('todos.submission.upload');
    Route::patch('todos/{todo}/submission/complete', [WorkspaceTaskSubmissionController::class, 'complete'])->name('todos.submission.complete');
    Route::delete('todos/{todo}/submission/files/{file}', [WorkspaceTaskSubmissionController::class, 'removeFile'])->name('todos.submission.files.destroy');
    Route::get('todos/{todo}/submission/files/{file}/download', [WorkspaceTaskSubmissionController::class, 'downloadFile'])->name('todos.submission.files.download');
    Route::get('todos/{todo}/assignments/{assignment}/download', [WorkspaceTaskSubmissionController::class, 'downloadAssignment'])->name('todos.assignments.download');
    Route::get('todos/{todo}/download-all', [WorkspaceTaskSubmissionController::class, 'downloadAll'])->name('todos.submissions.download-all');
    Route::get('todos/{todo}/collaboration', [WorkspaceCollaborationController::class, 'manageTodo'])->whereUuid('todo')->name('todos.collaboration.manage');
    Route::post('todos/{todo}/collaboration', [WorkspaceCollaborationController::class, 'addTodo'])->whereUuid('todo')->name('todos.collaboration.add');
    Route::delete('todos/{todo}/collaboration/{member}', [WorkspaceCollaborationController::class, 'removeTodo'])->whereUuid('todo')->whereUuid('member')->name('todos.collaboration.remove');
    Route::post('todos/{todo}/collaboration/entries', [WorkspaceCollaborationController::class, 'entryTodo'])->middleware('throttle:30,1')->whereUuid('todo')->name('todos.collaboration.entry');
    Route::get('todos/{todo}', [WorkspaceCollaborationController::class, 'showTodo'])->whereUuid('todo')->name('todos.show');
    Route::resource('todos', TodoController::class)->except(['show'])->parameters(['todos'=>'todo']);
});
