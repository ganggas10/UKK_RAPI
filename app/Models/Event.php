<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'room_id', 'user_id',
        'date', 'start_date', 'end_date', 'session', 'start_time', 'end_time', 'status',
        'rejection_message', 'rejection_notes',
        'move_reason', 'moved_to_room_id', 'moved_by',
    ];

    protected $casts = [
        'date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function scopeOverlapping(
        Builder $query,
        int $roomId,
        string $startDate,
        string $endDate,
        string $startTime,
        string $endTime,
        ?int $ignoreId = null,
    ): Builder {
        return $query
            ->where('room_id', $roomId)
            ->whereIn('status', ['pending', 'approved', 'moved'])
            ->when($ignoreId !== null, fn (Builder $builder) => $builder->where('id', '!=', $ignoreId))
            ->whereRaw('COALESCE(start_date, date) <= ?', [$endDate])
            ->whereRaw('COALESCE(end_date, date) >= ?', [$startDate])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);
    }

    public function bookingStartDate(): Carbon
    {
        return $this->start_date ?? $this->date;
    }

    public function bookingEndDate(): Carbon
    {
        return $this->end_date ?? $this->date;
    }

    public function bookingDurationInDays(): int
    {
        return $this->bookingStartDate()->diffInDays($this->bookingEndDate()) + 1;
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movedToRoom()
    {
        return $this->belongsTo(Room::class, 'moved_to_room_id');
    }

    public function movedByUser()
    {
        return $this->belongsTo(User::class, 'moved_by');
    }

    public function roomMoveHistories()
    {
        return $this->hasMany(RoomMoveHistory::class);
    }

    public function creatorRole(): string
    {
        return $this->user?->role ?? 'guru';
    }

    public function creatorRoleLabel(): string
    {
        return match ($this->creatorRole()) {
            'admin' => 'Waka / Admin',
            'guru' => 'Guru',
            'organisasi' => 'Organisasi',
            'pic' => 'PIC Ruang',
            default => ucfirst($this->creatorRole()),
        };
    }

    public function creatorColor(): string
    {
        return match ($this->creatorRole()) {
            'admin' => '#0d6efd',       // Biru
            'guru' => '#198754',        // Hijau
            'organisasi' => '#6f42c1',  // Ungu
            'pic' => '#fd7e14',         // Oranye
            default => '#0d6efd',
        };
    }

    public function creatorBorderColor(): string
    {
        return match ($this->creatorRole()) {
            'admin' => '#0a58ca',
            'guru' => '#146c43',
            'organisasi' => '#59359a',
            'pic' => '#e65c00',
            default => '#0a58ca',
        };
    }

    public function creatorBadgeStyle(): string
    {
        return "background-color: {$this->creatorColor()} !important; color: #ffffff;";
    }
}
