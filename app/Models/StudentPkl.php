<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentPkl extends Model
{
    use HasFactory;

    protected $table = 'student_pkl';

    protected $fillable = [
        'student_id',
        'tempat_pkl',
        'pembimbing_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function pembimbing(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'pembimbing_id');
    }

    public function locationLogs(): HasMany
    {
        return $this->hasMany(PklLocationLog::class, 'student_pkl_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    /** Alias kompatibilitas view student-pkl: company == tempat_pkl. */
    public function getCompanyAttribute(): ?string
    {
        return $this->tempat_pkl;
    }

    /** Alias kompatibilitas view student-pkl: nama pembimbing. */
    public function getSupervisorAttribute(): string
    {
        return $this->pembimbing?->user?->name ?? '-';
    }
}
