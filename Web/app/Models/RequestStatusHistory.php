<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_request_id',
        'changed_by',
        'previous_status',
        'new_status',
        'notes',
    ];

    public function studentRequest(): BelongsTo
    {
        return $this->belongsTo(StudentRequest::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
