<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Room;
use App\Models\SchoolSession;
use App\Models\User;
use Database\Seeders\SchoolSessionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolSessionAndBookingH1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SchoolSessionSeeder::class);
    }

    public function test_admin_can_add_new_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.sessions.store'), [
            'name' => 'Sesi Ekstra',
            'start_time' => '16:00',
            'end_time' => '17:30',
            'sort_order' => 12,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.sessions.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('school_sessions', [
            'name' => 'Sesi Ekstra',
            'start_time' => '16:00',
            'end_time' => '17:30',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_edit_session_times(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $session = SchoolSession::where('name', 'Sesi 1')->firstOrFail();

        $response = $this->actingAs($admin)->put(route('admin.sessions.update', $session), [
            'name' => 'Sesi 1 Pagi',
            'start_time' => '07:00',
            'end_time' => '08:15',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.sessions.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('school_sessions', [
            'id' => $session->id,
            'name' => 'Sesi 1 Pagi',
            'start_time' => '07:00',
            'end_time' => '08:15',
        ]);
    }

    public function test_admin_can_toggle_session_active_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $session = SchoolSession::where('name', 'Sesi 2')->firstOrFail();

        $this->assertTrue($session->is_active);

        // Toggle to inactive
        $this->actingAs($admin)->patch(route('admin.sessions.toggle', $session))
            ->assertRedirect(route('admin.sessions.index'))
            ->assertSessionHas('success');

        $this->assertFalse($session->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($admin)->patch(route('admin.sessions.toggle', $session));
        $this->assertTrue($session->fresh()->is_active);
    }

    public function test_admin_cannot_delete_session_used_in_active_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = Room::create(['name' => 'Lab Komputer', 'capacity' => 30]);
        $session = SchoolSession::where('name', 'Sesi 1')->firstOrFail();

        Event::create([
            'title' => 'Ujian Aktif',
            'room_id' => $room->id,
            'user_id' => $admin->id,
            'date' => now()->addDays(3)->toDateString(),
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'start_time' => '07:15',
            'end_time' => '08:00',
            'session' => 'Sesi 1 - Sesi 1',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.sessions.destroy', $session));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('school_sessions', ['id' => $session->id]);
    }

    public function test_admin_can_delete_unused_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $session = SchoolSession::create([
            'name' => 'Sesi Uji Coba',
            'start_time' => '16:00',
            'end_time' => '17:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.sessions.destroy', $session));
        $response->assertRedirect(route('admin.sessions.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('school_sessions', ['id' => $session->id]);
    }

    public function test_guru_create_view_only_displays_active_sessions(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $inactiveSession = SchoolSession::where('name', 'Sesi 3')->firstOrFail();
        $inactiveSession->update(['is_active' => false]);

        $response = $this->actingAs($guru)->get(route('guru.events.create'));
        $response->assertOk();
        $response->assertDontSee($inactiveSession->name.' ('.$inactiveSession->formatted_start_time);
        $response->assertSee('Sesi 1 (07:15 - 08:00)');
    }

    public function test_booking_on_same_day_today_is_rejected_due_to_h1_rule(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Ruang 1', 'capacity' => 36]);
        $today = now()->format('Y-m-d');

        $response = $this->actingAs($guru)->post(route('guru.events.store'), [
            'title' => 'Peminjaman Hari Ini',
            'room_id' => $room->id,
            'start_date' => $today,
            'end_date' => $today,
            'start_session' => 'Sesi 1',
            'end_session' => 'Sesi 2',
        ]);

        $response->assertSessionHasErrors(['start_date']);
        $this->assertDatabaseMissing('events', ['title' => 'Peminjaman Hari Ini']);
    }

    public function test_booking_at_least_h1_ahead_is_accepted(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Ruang 1', 'capacity' => 36]);
        $tomorrow = now()->addDay()->format('Y-m-d');

        $response = $this->actingAs($guru)->post(route('guru.events.store'), [
            'title' => 'Peminjaman Besok',
            'room_id' => $room->id,
            'start_date' => $tomorrow,
            'end_date' => $tomorrow,
            'start_session' => 'Sesi 1',
            'end_session' => 'Sesi 2',
        ]);

        $response->assertRedirect(route('guru.events.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('events', [
            'title' => 'Peminjaman Besok',
            'start_date' => $tomorrow.' 00:00:00',
            'start_time' => '07:15',
            'end_time' => '08:45',
            'session' => 'Sesi 1 - Sesi 2',
        ]);
    }

    public function test_booking_with_inactive_session_is_rejected(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Ruang 1', 'capacity' => 36]);
        $tomorrow = now()->addDay()->format('Y-m-d');

        $session = SchoolSession::where('name', 'Sesi 4')->firstOrFail();
        $session->update(['is_active' => false]);

        $response = $this->actingAs($guru)->post(route('guru.events.store'), [
            'title' => 'Peminjaman Sesi Nonaktif',
            'room_id' => $room->id,
            'start_date' => $tomorrow,
            'end_date' => $tomorrow,
            'start_session' => 'Sesi 4',
            'end_session' => 'Sesi 4',
        ]);

        $response->assertSessionHasErrors(['start_session']);
    }

    public function test_editing_session_does_not_mutate_past_approved_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = Room::create(['name' => 'Ruang 1', 'capacity' => 36]);
        $session = SchoolSession::where('name', 'Sesi 1')->firstOrFail();

        $event = Event::create([
            'title' => 'Kegiatan Masa Lalu',
            'room_id' => $room->id,
            'user_id' => $admin->id,
            'date' => '2026-10-01',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-01',
            'start_time' => '07:15:00',
            'end_time' => '08:00:00',
            'session' => 'Sesi 1 - Sesi 1',
            'status' => 'approved',
        ]);

        // Ubah jam sesi 1 menjadi 06:30 - 07:30
        $this->actingAs($admin)->put(route('admin.sessions.update', $session), [
            'name' => 'Sesi 1',
            'start_time' => '06:30',
            'end_time' => '07:30',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Pastikan event lama tetap memiliki jam 07:15:00 - 08:00:00
        $this->assertSame('07:15:00', $event->fresh()->start_time);
        $this->assertSame('08:00:00', $event->fresh()->end_time);
        $this->assertSame('Sesi 1 - Sesi 1', $event->fresh()->session);
    }
}
