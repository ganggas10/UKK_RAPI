<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\SchoolSessionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SchoolSessionSeeder::class);
    }

    // =========================================================================
    // 1. PENGUJIAN FILTER GURU / ORGANISASI
    // =========================================================================

    public function test_guru_only_sees_their_own_events_and_data_isolation_is_maintained(): void
    {
        $guru1 = User::factory()->create(['role' => 'guru', 'name' => 'Guru Satu']);
        $guru2 = User::factory()->create(['role' => 'guru', 'name' => 'Guru Dua']);
        $room = Room::create(['name' => 'Lab Komputer 1', 'capacity' => 36]);

        $eventGuru1 = Event::create([
            'title' => 'Praktikum Guru Satu',
            'room_id' => $room->id,
            'user_id' => $guru1->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'pending',
        ]);

        $eventGuru2 = Event::create([
            'title' => 'Praktikum Guru Dua',
            'room_id' => $room->id,
            'user_id' => $guru2->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'date' => now()->addDays(3)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($guru1)->get(route('guru.events.index'));

        $response->assertOk()
            ->assertSee('Praktikum Guru Satu')
            ->assertDontSee('Praktikum Guru Dua');
    }

    public function test_guru_can_filter_events_by_search_title(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Lab Biologi', 'capacity' => 30]);

        Event::create([
            'title' => 'Ujian Kimia Semester',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        Event::create([
            'title' => 'Rapat Ekskul Pramuka',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'date' => now()->addDays(3)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'session' => 'Sesi 3 - Sesi 4',
            'status' => 'pending',
        ]);

        // Filter pencarian "Kimia"
        $response = $this->actingAs($guru)->get(route('guru.events.index', ['search' => 'Kimia']));

        $response->assertOk()
            ->assertSee('Ujian Kimia Semester')
            ->assertDontSee('Rapat Ekskul Pramuka');
    }

    public function test_guru_can_filter_events_by_status(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Aula', 'capacity' => 100]);

        Event::create([
            'title' => 'Peminjaman Pending',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'pending',
        ]);

        Event::create([
            'title' => 'Peminjaman Approved',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'date' => now()->addDays(3)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        // Filter status 'pending'
        $response = $this->actingAs($guru)->get(route('guru.events.index', ['status' => 'pending']));

        $response->assertOk()
            ->assertSee('Peminjaman Pending')
            ->assertDontSee('Peminjaman Approved');
    }

    public function test_guru_can_filter_events_by_room(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $roomA = Room::create(['name' => 'Ruang Teori A', 'capacity' => 36]);
        $roomB = Room::create(['name' => 'Ruang Teori B', 'capacity' => 36]);

        Event::create([
            'title' => 'Kegiatan di Ruang A',
            'room_id' => $roomA->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'pending',
        ]);

        Event::create([
            'title' => 'Kegiatan di Ruang B',
            'room_id' => $roomB->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'pending',
        ]);

        // Filter room_id
        $response = $this->actingAs($guru)->get(route('guru.events.index', ['room_id' => $roomA->id]));

        $response->assertOk()
            ->assertSee('Kegiatan di Ruang A')
            ->assertDontSee('Kegiatan di Ruang B');
    }

    public function test_guru_can_filter_events_by_date_range(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Lab Bahasa', 'capacity' => 32]);

        Event::create([
            'title' => 'Kegiatan Minggu Depan',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-21',
            'date' => '2026-11-20',
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        Event::create([
            'title' => 'Kegiatan Bulan Depan',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'start_date' => '2026-12-10',
            'end_date' => '2026-12-11',
            'date' => '2026-12-10',
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        // Filter rentang 2026-11-19 s/d 2026-11-25
        $response = $this->actingAs($guru)->get(route('guru.events.index', [
            'date_from' => '2026-11-19',
            'date_to' => '2026-11-25',
        ]));

        $response->assertOk()
            ->assertSee('Kegiatan Minggu Depan')
            ->assertDontSee('Kegiatan Bulan Depan');
    }

    public function test_guru_combining_all_filters_and_pagination_query_string(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Lab Robotik', 'capacity' => 20]);

        Event::create([
            'title' => 'Lomba Robotik Regional',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-20',
            'date' => '2026-11-20',
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($guru)->get(route('guru.events.index', [
            'search' => 'Robotik',
            'status' => 'approved',
            'room_id' => $room->id,
            'date_from' => '2026-11-20',
            'date_to' => '2026-11-20',
        ]));

        $response->assertOk()
            ->assertSee('Lomba Robotik Regional');
    }

    public function test_guru_filter_date_to_before_date_from_shows_validation_error(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);

        $response = $this->actingAs($guru)->get(route('guru.events.index', [
            'date_from' => '2026-11-25',
            'date_to' => '2026-11-20',
        ]));

        $response->assertSessionHasErrors(['date_to']);
    }

    public function test_organisasi_role_can_filter_their_own_events(): void
    {
        $organisasi = User::factory()->create(['role' => 'organisasi', 'name' => 'MPK Sekolah']);
        $otherUser = User::factory()->create(['role' => 'guru', 'name' => 'Pak Guru']);
        $room = Room::create(['name' => 'Ruang OSIS', 'capacity' => 20]);

        Event::create([
            'title' => 'Sidang Pleno MPK',
            'room_id' => $room->id,
            'user_id' => $organisasi->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'pending',
        ]);

        Event::create([
            'title' => 'Rapat Guru Umum',
            'room_id' => $room->id,
            'user_id' => $otherUser->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($organisasi)->get(route('guru.events.index', [
            'search' => 'Pleno',
        ]));

        $response->assertOk()
            ->assertSee('Sidang Pleno MPK')
            ->assertDontSee('Rapat Guru Umum');
    }

    // =========================================================================
    // 2. PENGUJIAN FILTER WAKA / ADMIN
    // =========================================================================

    public function test_admin_can_search_by_event_title_and_applicant_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guruA = User::factory()->create(['role' => 'guru', 'name' => 'Budi Santoso']);
        $guruB = User::factory()->create(['role' => 'guru', 'name' => 'Siti Rahma']);
        $room = Room::create(['name' => 'Aula Graha', 'capacity' => 150]);

        Event::create([
            'title' => 'Workshop Literasi Digital',
            'room_id' => $room->id,
            'user_id' => $guruA->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'pending',
        ]);

        Event::create([
            'title' => 'Seminar Sains Madrasah',
            'room_id' => $room->id,
            'user_id' => $guruB->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'date' => now()->addDays(3)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        // 1. Cari berdasarkan judul
        $response1 = $this->actingAs($admin)->get(route('admin.events.index', ['search' => 'Literasi']));
        $response1->assertOk()
            ->assertSee('Workshop Literasi Digital')
            ->assertDontSee('Seminar Sains Madrasah');

        // 2. Cari berdasarkan nama pengaju "Siti Rahma"
        $response2 = $this->actingAs($admin)->get(route('admin.events.index', ['search' => 'Siti Rahma']));
        $response2->assertOk()
            ->assertSee('Seminar Sains Madrasah')
            ->assertDontSee('Workshop Literasi Digital');
    }

    public function test_admin_can_filter_by_applicant_user_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $organisasi = User::factory()->create(['role' => 'organisasi', 'name' => 'OSIS SMKN 1']);
        $guru = User::factory()->create(['role' => 'guru', 'name' => 'Pak Joko']);
        $room = Room::create(['name' => 'Ruang Musik', 'capacity' => 25]);

        Event::create([
            'title' => 'Latihan Band OSIS',
            'room_id' => $room->id,
            'user_id' => $organisasi->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'pending',
        ]);

        Event::create([
            'title' => 'Praktek Seni Budaya',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'date' => now()->addDays(3)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        // Filter pengaju user_id = organisasi
        $response = $this->actingAs($admin)->get(route('admin.events.index', ['user_id' => $organisasi->id]));

        $response->assertOk()
            ->assertSee('Latihan Band OSIS')
            ->assertDontSee('Praktek Seni Budaya');
    }

    public function test_admin_can_filter_by_status_and_room(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room1 = Room::create(['name' => 'Lab RPL 1', 'capacity' => 36]);
        $room2 = Room::create(['name' => 'Lab RPL 2', 'capacity' => 36]);

        Event::create([
            'title' => 'Event Lab 1 Pending',
            'room_id' => $room1->id,
            'user_id' => $admin->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'pending',
        ]);

        Event::create([
            'title' => 'Event Lab 2 Pending',
            'room_id' => $room2->id,
            'user_id' => $admin->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.index', [
            'status' => 'pending',
            'room_id' => $room1->id,
        ]));

        $response->assertOk()
            ->assertSee('Event Lab 1 Pending')
            ->assertDontSee('Event Lab 2 Pending');
    }

    public function test_admin_actions_approve_reject_and_move_remain_fully_functional(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $roomA = Room::create(['name' => 'Lab Mesin 1', 'capacity' => 30]);
        $roomB = Room::create(['name' => 'Lab Mesin 2', 'capacity' => 30]);

        $event = Event::create([
            'title' => 'Uji Kompetensi Kejuruan',
            'room_id' => $roomA->id,
            'user_id' => $admin->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'pending',
        ]);

        // 1. Approve
        $this->actingAs($admin)->patch(route('admin.events.update', $event), [
            'status' => 'approved',
        ])->assertRedirect(route('admin.events.index'));

        $this->assertSame('approved', $event->fresh()->status);

        // 2. Move
        $this->actingAs($admin)->post(route('admin.events.move', $event), [
            'new_room_id' => $roomB->id,
            'move_reason' => 'Perbaikan instalasi listrik mendadak di Lab Mesin 1',
        ])->assertRedirect(route('admin.events.index'));

        $this->assertSame('moved', $event->fresh()->status);
        $this->assertSame($roomB->id, $event->fresh()->room_id);
    }

    public function test_admin_filter_date_to_before_date_from_shows_validation_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.events.index', [
            'date_from' => '2026-12-15',
            'date_to' => '2026-12-10',
        ]));

        $response->assertSessionHasErrors(['date_to']);
    }

    public function test_admin_can_combine_all_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guru = User::factory()->create(['role' => 'guru', 'name' => 'Guru Fisika']);
        $room = Room::create(['name' => 'Lab Fisika', 'capacity' => 32]);

        Event::create([
            'title' => 'Eksperimen Optik',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'start_date' => '2026-11-22',
            'end_date' => '2026-11-22',
            'date' => '2026-11-22',
            'start_time' => '07:15:00',
            'end_time' => '08:45:00',
            'session' => 'Sesi 1 - Sesi 2',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.index', [
            'search' => 'Optik',
            'status' => 'approved',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'date_from' => '2026-11-22',
            'date_to' => '2026-11-22',
        ]));

        $response->assertOk()
            ->assertSee('Eksperimen Optik')
            ->assertSee('Guru Fisika')
            ->assertSee('Lab Fisika');
    }
}
