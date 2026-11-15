<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkspaceTaskSubmissionFile extends Model
{
    use HasUuids;
    protected $table = 'workspace_task_submission_files';
    protected $primaryKey = 'file_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['assignment_id','uploaded_by_user_id','original_name','stored_path','mime_type','file_size'];

    public function assignment() { return $this->belongsTo(WorkspaceTaskAssignment::class, 'assignment_id', 'assignment_id'); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by_user_id', 'user_id'); }
}
