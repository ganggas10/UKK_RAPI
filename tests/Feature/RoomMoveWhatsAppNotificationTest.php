<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Room;
use App\Models\RoomMoveHistory;
use App\Models\User;
use Database\Seeders\SchoolSessionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomMoveWhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SchoolSessionSeeder::class);
    }

    public function test_moving_room_creates_history_record_and_sets_wa_confirmation_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guru = User::factory()->create(['role' => 'guru', 'name' => 'Budi Guru', 'phone' => '081234567890']);
        $roomA = Room::create(['name' => 'Lab Fisika', 'capacity' => 30]);
        $roomB = Room::create(['name' => 'Lab Biologi', 'capacity' => 30]);

        $event = Event::create([
            'title' => 'Praktikum Gelombang',
            'room_id' => $roomA->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.events.move', $event), [
            'new_room_id' => $roomB->id,
            'move_reason' => 'Ada perbaikan plafon mendadak di Lab Fisika.',
        ]);

        $response->assertRedirect(route('admin.events.index'));
        $response->assertSessionHas('wa_confirm_move_id');
        $response->assertSessionHas('success');

        // Pastikan event terupdate
        $this->assertSame($roomB->id, $event->fresh()->room_id);
        $this->assertSame('moved', $event->fresh()->status);

        // Pastikan RoomMoveHistory tercatat
        $this->assertDatabaseHas('room_move_histories', [
            'event_id' => $event->id,
            'user_id' => $guru->id,
            'admin_id' => $admin->id,
            'old_room_name' => 'Lab Fisika',
            'new_room_name' => 'Lab Biologi',
            'target_phone' => '081234567890',
            'wa_status' => 'belum_dikirim',
        ]);

        $history = RoomMoveHistory::where('event_id', $event->id)->firstOrFail();
        $this->assertStringContainsString('PEMBERITAHUAN PEMINDAHAN RUANGAN', $history->wa_message);
        $this->assertStringContainsString('Budi Guru', $history->wa_message);
        $this->assertStringContainsString('Praktikum Gelombang', $history->wa_message);
        $this->assertStringContainsString('Lab Fisika', $history->wa_message);
        $this->assertStringContainsString('Lab Biologi', $history->wa_message);
        $this->assertStringContainsString('Ada perbaikan plafon mendadak', $history->wa_message);
    }

    public function test_admin_sees_wa_confirmation_modal_when_session_is_present(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guru = User::factory()->create(['role' => 'guru', 'name' => 'Ahmad Guru', 'phone' => '081298765432']);
        $roomA = Room::create(['name' => 'Ruang 10', 'capacity' => 36]);
        $roomB = Room::create(['name' => 'Ruang 11', 'capacity' => 36]);

        $event = Event::create([
            'title' => 'Rapat Wali Murid',
            'room_id' => $roomA->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        $history = RoomMoveHistory::create([
            'event_id' => $event->id,
            'user_id' => $guru->id,
            'admin_id' => $admin->id,
            'old_room_id' => $roomA->id,
            'new_room_id' => $roomB->id,
            'old_room_name' => 'Ruang 10',
            'new_room_name' => 'Ruang 11',
            'target_phone' => '081298765432',
            'move_reason' => 'Penyesuaian kapasitas peserta rapat.',
            'wa_message' => RoomMoveHistory::generateWhatsAppMessage($event, 'Ruang 10', 'Ruang 11', 'Penyesuaian kapasitas peserta rapat.'),
            'wa_status' => 'belum_dikirim',
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['wa_confirm_move_id' => $history->id])
            ->get(route('admin.events.index'));

        $response->assertOk()
            ->assertSee('Konfirmasi Pengiriman WhatsApp')
            ->assertSee('Ahmad Guru')
            ->assertSee('+6281298765432')
            ->assertSee('Ruang 10')
            ->assertSee('Ruang 11')
            ->assertSee('Penyesuaian kapasitas peserta rapat.')
            ->assertSee('Kirim WhatsApp')
            ->assertSee('Batal');
    }

    public function test_admin_can_send_whatsapp_and_status_changes_to_berhasil_dikirim(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guru = User::factory()->create(['role' => 'guru', 'name' => 'Siti Guru', 'phone' => '085711223344']);
        $roomA = Room::create(['name' => 'Lab Komputer A', 'capacity' => 30]);
        $roomB = Room::create(['name' => 'Lab Komputer B', 'capacity' => 30]);

        $event = Event::create([
            'title' => 'Simulasi UTBK',
            'room_id' => $roomA->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        $history = RoomMoveHistory::create([
            'event_id' => $event->id,
            'user_id' => $guru->id,
            'admin_id' => $admin->id,
            'old_room_id' => $roomA->id,
            'new_room_id' => $roomB->id,
            'old_room_name' => 'Lab Komputer A',
            'new_room_name' => 'Lab Komputer B',
            'target_phone' => '085711223344',
            'move_reason' => 'Perangkat PC di Lab A sedang maintenance.',
            'wa_message' => RoomMoveHistory::generateWhatsAppMessage($event, 'Lab Komputer A', 'Lab Komputer B', 'Perangkat PC di Lab A sedang maintenance.'),
            'wa_status' => 'belum_dikirim',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.move.whatsapp', $history));

        // Harus redirect ke URL wa.me dengan nomor format 6285711223344
        $response->assertRedirect();
        $this->assertStringContainsString('https://wa.me/6285711223344', $response->getTargetUrl());

        // Status WA harus berubah menjadi berhasil_dikirim
        $this->assertSame('berhasil_dikirim', $history->fresh()->wa_status);
        $this->assertNotNull($history->fresh()->wa_sent_at);
    }

    public function test_admin_cancelling_whatsapp_keeps_room_change_and_marks_status_as_belum_dikirim(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guru = User::factory()->create(['role' => 'guru', 'name' => 'Rina Guru', 'phone' => '081234567890']);
        $roomA = Room::create(['name' => 'Ruang A', 'capacity' => 30]);
        $roomB = Room::create(['name' => 'Ruang B', 'capacity' => 30]);

        $event = Event::create([
            'title' => 'Tes Bakat Minat',
            'room_id' => $roomB->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'moved',
        ]);

        $history = RoomMoveHistory::create([
            'event_id' => $event->id,
            'user_id' => $guru->id,
            'admin_id' => $admin->id,
            'old_room_id' => $roomA->id,
            'new_room_id' => $roomB->id,
            'old_room_name' => 'Ruang A',
            'new_room_name' => 'Ruang B',
            'target_phone' => '081234567890',
            'move_reason' => 'Perubahan jadwal internal.',
            'wa_message' => 'Test message',
            'wa_status' => 'belum_dikirim',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.events.move.cancel-whatsapp', $history));

        $response->assertRedirect(route('admin.events.index'));
        $response->assertSessionHas('info');

        // Status WA tetap belum_dikirim
        $this->assertSame('belum_dikirim', $history->fresh()->wa_status);
        $this->assertNull($history->fresh()->wa_sent_at);

        // Ruangan tetap tersimpan sebagai Ruang B
        $this->assertSame($roomB->id, $event->fresh()->room_id);
    }

    public function test_when_recipient_phone_is_missing_system_warns_and_prevents_sending(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guruNoPhone = User::factory()->create(['role' => 'guru', 'name' => 'Guru Tanpa HP', 'phone' => null]);
        $roomA = Room::create(['name' => 'Ruang 5', 'capacity' => 30]);
        $roomB = Room::create(['name' => 'Ruang 6', 'capacity' => 30]);

        $event = Event::create([
            'title' => 'Pertemuan OSIS',
            'room_id' => $roomB->id,
            'user_id' => $guruNoPhone->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'moved',
        ]);

        $history = RoomMoveHistory::create([
            'event_id' => $event->id,
            'user_id' => $guruNoPhone->id,
            'admin_id' => $admin->id,
            'old_room_id' => $roomA->id,
            'new_room_id' => $roomB->id,
            'old_room_name' => 'Ruang 5',
            'new_room_name' => 'Ruang 6',
            'target_phone' => null,
            'move_reason' => 'Kebutuhan proyektor.',
            'wa_message' => 'Pesan tanpa nomor HP',
            'wa_status' => 'belum_dikirim',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.move.whatsapp', $history));

        $response->assertRedirect(route('admin.events.index'));
        $response->assertSessionHas('error');
        $this->assertSame('gagal_dikirim', $history->fresh()->wa_status);
    }

    public function test_admin_can_view_room_move_history_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guru = User::factory()->create(['role' => 'guru', 'name' => 'Eko Prasetyo']);
        $room1 = Room::create(['name' => 'Lab 1', 'capacity' => 30]);
        $room2 = Room::create(['name' => 'Lab 2', 'capacity' => 30]);

        $event = Event::create([
            'title' => 'Pelatihan Robotik',
            'room_id' => $room2->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'moved',
        ]);

        RoomMoveHistory::create([
            'event_id' => $event->id,
            'user_id' => $guru->id,
            'admin_id' => $admin->id,
            'old_room_id' => $room1->id,
            'new_room_id' => $room2->id,
            'old_room_name' => 'Lab 1',
            'new_room_name' => 'Lab 2',
            'target_phone' => '081234567890',
            'move_reason' => 'Komputer di Lab 1 rusak.',
            'wa_message' => 'Pemberitahuan pindah ruang',
            'wa_status' => 'berhasil_dikirim',
            'wa_sent_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.move-history'));

        $response->assertOk()
            ->assertSee('Riwayat Pemindahan Ruangan')
            ->assertSee('Pelatihan Robotik')
            ->assertSee('Eko Prasetyo')
            ->assertSee('Lab 1')
            ->assertSee('Lab 2')
            ->assertSee('Berhasil Dikirim');
    }
}
