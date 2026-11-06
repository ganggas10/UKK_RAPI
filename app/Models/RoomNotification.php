<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomNotification extends Model
{
    use HasFactory;

    protected $table = 'room_notifications';

    protected $fillable = ['user_id', 'title', 'message', 'is_read', 'event_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
