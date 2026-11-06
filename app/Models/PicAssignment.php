<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PicAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id', 'pic_id', 'starts_at', 'ends_at', 'status',
        'notes', 'replacement_reason', 'assigned_by',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
