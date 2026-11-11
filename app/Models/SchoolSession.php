<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope untuk sesi aktif
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Format jam mulai (HH:mm)
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return substr($this->start_time, 0, 5);
    }

    /**
     * Format jam selesai (HH:mm)
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return substr($this->end_time, 0, 5);
    }

    /**
     * Rentang jam (contoh: "07:15 - 08:00")
     */
    public function getTimeRangeAttribute(): string
    {
        return "{$this->formatted_start_time} - {$this->formatted_end_time}";
    }

    /**
     * Cek apakah sesi ini sedang terhubung dengan agenda/peminjaman aktif
     * (status pending, approved, atau moved).
     */
    public function isUsedInActiveEvents(): bool
    {
        return Event::whereIn('status', ['pending', 'approved', 'moved'])
            ->where(function ($query) {
                $query->where('session', 'like', "%{$this->name}%")
                    ->orWhere(function ($q) {
                        $q->where('start_time', '<=', $this->end_time)
                            ->where('end_time', '>=', $this->start_time);
                    });
            })
            ->exists();
    }
}
