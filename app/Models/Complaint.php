<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id', 'reporter_id', 'description',
        'photo_path', 'status_repair', 'pic_notes',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
