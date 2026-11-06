<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location', 'capacity', 'pic_id'];

    protected $appends = ['category'];

    public function getCategoryAttribute(): string
    {
        return $this->location ?: 'Lainnya';
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function activePicAssignments()
    {
        return $this->hasMany(PicAssignment::class)->where('status', 'aktif');
    }

    public function activePics()
    {
        return $this->belongsToMany(User::class, 'pic_assignments', 'room_id', 'pic_id')
            ->wherePivot('status', 'aktif');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function picAssignments()
    {
        return $this->hasMany(PicAssignment::class);
    }
}
