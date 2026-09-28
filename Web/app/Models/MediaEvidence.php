<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaEvidence extends Model
{
    use HasFactory;

    protected $table = 'media_evidences';

    protected $fillable = [
        'student_request_id',
        'uploaded_by',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    public function studentRequest(): BelongsTo
    {
        return $this->belongsTo(StudentRequest::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
