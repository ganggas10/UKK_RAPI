<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomMoveHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'admin_id',
        'old_room_id',
        'new_room_id',
        'old_room_name',
        'new_room_name',
        'target_phone',
        'move_reason',
        'wa_message',
        'wa_status',
        'wa_sent_at',
    ];

    protected $casts = [
        'wa_sent_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function oldRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'old_room_id');
    }

    public function newRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'new_room_id');
    }

    public function formattedPhone(): ?string
    {
        return self::formatWhatsAppNumber($this->target_phone);
    }

    public function hasValidPhone(): bool
    {
        return ! empty($this->formattedPhone());
    }

    public function statusBadgeClass(): string
    {
        return match ($this->wa_status) {
            'berhasil_dikirim' => 'success',
            'gagal_dikirim' => 'danger',
            default => 'warning text-dark',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->wa_status) {
            'berhasil_dikirim' => 'Berhasil Dikirim',
            'gagal_dikirim' => 'Gagal Dikirim',
            default => 'Belum Dikirim',
        };
    }

    public function getWhatsAppUrl(): ?string
    {
        $phone = $this->formattedPhone();
        if (! $phone) {
            return null;
        }

        return 'https://wa.me/'.$phone.'?text='.urlencode($this->wa_message ?? '');
    }

    public static function formatWhatsAppNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (empty($clean)) {
            return null;
        }

        if (str_starts_with($clean, '08')) {
            $clean = '62'.substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62'.$clean;
        }

        return strlen($clean) >= 9 ? $clean : null;
    }

    public static function generateWhatsAppMessage(
        Event $event,
        string $oldRoomName,
        string $newRoomName,
        string $moveReason,
    ): string {
        $recipientName = $event->user?->name ?? 'Bapak/Ibu Pengaju';
        $startDate = $event->bookingStartDate()->format('d/m/Y');
        $endDate = $event->bookingEndDate()->format('d/m/Y');
        $dateText = ($startDate === $endDate) ? $startDate : "{$startDate} - {$endDate}";

        $sessionInfo = $event->session
            ? "{$event->session} (".substr($event->start_time, 0, 5).' - '.substr($event->end_time, 0, 5).' WIB)'
            : (substr($event->start_time, 0, 5).' - '.substr($event->end_time, 0, 5).' WIB');

        return "PEMBERITAHUAN PEMINDAHAN RUANGAN\n\n".
            "Yth. Bapak/Ibu {$recipientName},\n\n".
            "Kami informasikan bahwa terdapat perubahan ruangan untuk agenda peminjaman Anda di sistem RAPI-Sekolah.\n\n".
            "Berikut detail perubahan:\n\n".
            "- Nama Kegiatan: {$event->title}\n".
            "- Ruangan Sebelumnya: {$oldRoomName}\n".
            "- Ruangan Pengganti: {$newRoomName}\n".
            "- Tanggal Penggunaan: {$dateText}\n".
            "- Sesi: {$sessionInfo}\n\n".
            "Alasan Pemindahan:\n".
            "{$moveReason}\n\n".
            "Mohon untuk memperhatikan perubahan ruangan tersebut dan menggunakan ruangan pengganti sesuai jadwal yang telah ditentukan.\n\n".
            "Terima kasih atas perhatian dan kerja samanya.\n\n".
            "Salam,\n".
            'Administrasi RAPI-Sekolah.';
    }
}
