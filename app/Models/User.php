<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    // Role check helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPic(): bool
    {
        return $this->role === 'pic';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isOrganisasi(): bool
    {
        return $this->role === 'organisasi';
    }

    public function isMobileUser(): bool
    {
        return in_array($this->role, ['guru', 'organisasi', 'siswa'], true);
    }

    /**
     * Cek apakah PIC masih bertanggung jawab atas minimal satu ruangan,
     * baik sebagai PIC utama (rooms.pic_id) maupun penugasan aktif (pic_assignments).
     */
    public function hasActiveRoomResponsibility(): bool
    {
        return $this->rooms()->exists()
            || $this->picAssignments()->where('status', 'aktif')->exists();
    }

    // Relasi
    public function rooms()
    {
        return $this->hasMany(Room::class, 'pic_id');
    }

    public function picAssignments()
    {
        return $this->hasMany(PicAssignment::class, 'pic_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'user_id');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'reporter_id');
    }

    public function notifications_()
    {
        return $this->hasMany(RoomNotification::class, 'user_id');
    }
}
