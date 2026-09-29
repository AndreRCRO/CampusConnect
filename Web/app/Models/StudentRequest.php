<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_code',
        'student_id',
        'institutional_resource_id',
        'category',
        'location',
        'title',
        'description',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function institutionalResource(): BelongsTo
    {
        return $this->belongsTo(InstitutionalResource::class, 'institutional_resource_id');
    }

    public function mediaEvidences(): HasMany
    {
        return $this->hasMany(MediaEvidence::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RequestComment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class);
    }
}
