<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\NationalHoliday;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\NationalHolidaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NationalCalendarAndAgendaColorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NationalHolidaySeeder::class);
    }

    public function test_calendar_public_page_is_accessible(): void
    {
        $response = $this->get(route('calendar.public'));

        $response->assertStatus(200);
        $response->assertSee('Kalender Agenda', false);
        $response->assertSee('Waka / Admin (Biru)');
        $response->assertSee('Guru (Hijau)');
        $response->assertSee('Organisasi (Ungu)');
        $response->assertSee('Libur Nasional (Merah)');
    }

    public function test_august_17_is_marked_as_hari_kemerdekaan_libur_nasional(): void
    {
        $response = $this->getJson(route('calendar.events'));

        $response->assertStatus(200);

        $holidays = collect($response->json())
            ->where('extendedProps.type', 'national')
            ->where('extendedProps.date', '2026-08-17');

        $this->assertNotEmpty($holidays);
        $item = $holidays->first();
        $this->assertStringContainsString('Hari Kemerdekaan', $item['extendedProps']['name']);
        $this->assertEquals(NationalHoliday::CATEGORY_LIBUR_NASIONAL, $item['extendedProps']['category']);
        $this->assertEquals('#dc3545', $item['extendedProps']['color']);
        $this->assertTrue($item['extendedProps']['is_holiday']);
    }

    public function test_october_28_is_marked_as_hari_sumpah_pemuda(): void
    {
        $response = $this->getJson(route('calendar.events'));

        $response->assertStatus(200);

        $holidays = collect($response->json())
            ->where('extendedProps.type', 'national')
            ->where('extendedProps.date', '2026-10-28');

        $this->assertNotEmpty($holidays);
        $item = $holidays->first();
        $this->assertStringContainsString('Hari Sumpah Pemuda', $item['extendedProps']['name']);
        $this->assertEquals(NationalHoliday::CATEGORY_PERINGATAN_NASIONAL, $item['extendedProps']['category']);
        $this->assertEquals('#0d6efd', $item['extendedProps']['color']);
        $this->assertFalse($item['extendedProps']['is_holiday']);
    }

    public function test_november_10_is_marked_as_hari_pahlawan(): void
    {
        $response = $this->getJson(route('calendar.events'));

        $response->assertStatus(200);

        $holidays = collect($response->json())
            ->where('extendedProps.type', 'national')
            ->where('extendedProps.date', '2026-11-10');

        $this->assertNotEmpty($holidays);
        $item = $holidays->first();
        $this->assertStringContainsString('Hari Pahlawan', $item['extendedProps']['name']);
        $this->assertEquals(NationalHoliday::CATEGORY_PERINGATAN_NASIONAL, $item['extendedProps']['category']);
        $this->assertEquals('#0d6efd', $item['extendedProps']['color']);
        $this->assertFalse($item['extendedProps']['is_holiday']);
    }

    public function test_agenda_created_by_admin_is_colored_blue(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = Room::create(['name' => 'Ruang Waka', 'capacity' => 20]);

        Event::create([
            'title' => 'Rapat Koordinasi Waka',
            'room_id' => $room->id,
            'user_id' => $admin->id,
            'date' => '2026-11-12',
            'start_date' => '2026-11-12',
            'end_date' => '2026-11-12',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => 'approved',
        ]);

        $response = $this->getJson(route('calendar.events'));
        $agenda = collect($response->json())->firstWhere('extendedProps.title', 'Rapat Koordinasi Waka');

        $this->assertNotNull($agenda);
        $this->assertEquals('#0d6efd', $agenda['backgroundColor']); // Biru
        $this->assertEquals('admin', $agenda['extendedProps']['creator_role']);
        $this->assertEquals('Waka / Admin', $agenda['extendedProps']['creator_role_label']);
    }

    public function test_agenda_created_by_guru_is_colored_green(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Lab Komputer', 'capacity' => 35]);

        Event::create([
            'title' => 'Bimbingan Konseling Kelas',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'date' => '2026-11-15',
            'start_date' => '2026-11-15',
            'end_date' => '2026-11-15',
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'status' => 'approved',
        ]);

        $response = $this->getJson(route('calendar.events'));
        $agenda = collect($response->json())->firstWhere('extendedProps.title', 'Bimbingan Konseling Kelas');

        $this->assertNotNull($agenda);
        $this->assertEquals('#198754', $agenda['backgroundColor']); // Hijau
        $this->assertEquals('guru', $agenda['extendedProps']['creator_role']);
        $this->assertEquals('Guru', $agenda['extendedProps']['creator_role_label']);
    }

    public function test_agenda_created_by_organisasi_is_colored_purple(): void
    {
        $organisasi = User::factory()->create(['role' => 'organisasi']);
        $room = Room::create(['name' => 'Ruang OSIS', 'capacity' => 25]);

        Event::create([
            'title' => 'Latihan Rutin OSIS',
            'room_id' => $room->id,
            'user_id' => $organisasi->id,
            'date' => '2026-11-18',
            'start_date' => '2026-11-18',
            'end_date' => '2026-11-18',
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'status' => 'approved',
        ]);

        $response = $this->getJson(route('calendar.events'));
        $agenda = collect($response->json())->firstWhere('extendedProps.title', 'Latihan Rutin OSIS');

        $this->assertNotNull($agenda);
        $this->assertEquals('#6f42c1', $agenda['backgroundColor']); // Ungu
        $this->assertEquals('organisasi', $agenda['extendedProps']['creator_role']);
        $this->assertEquals('Organisasi', $agenda['extendedProps']['creator_role_label']);
    }

    public function test_multi_day_agenda_has_consistent_color_and_duration(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Aula Utama', 'capacity' => 150]);

        Event::create([
            'title' => 'Pelatihan Guru Kurikulum Merdeka',
            'room_id' => $room->id,
            'user_id' => $guru->id,
            'date' => '2026-11-10',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-13',
            'start_time' => '08:00:00',
            'end_time' => '15:00:00',
            'status' => 'approved',
        ]);

        $response = $this->getJson(route('calendar.events'));
        $agenda = collect($response->json())->firstWhere('extendedProps.title', 'Pelatihan Guru Kurikulum Merdeka');

        $this->assertNotNull($agenda);
        $this->assertEquals('#198754', $agenda['backgroundColor']);
        $this->assertEquals('2026-11-10T08:00:00', $agenda['start']);
        $this->assertEquals('2026-11-14T15:00:00', $agenda['end']);
        $this->assertEquals(4, $agenda['extendedProps']['duration_days']);
    }

    public function test_admin_can_manage_national_holidays_crud(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Akses index
        $this->actingAs($admin)
            ->get(route('admin.national-holidays.index'))
            ->assertStatus(200)
            ->assertSee('Hari Penting & Libur Nasional');

        // Tambah hari penting baru
        $storeResponse = $this->actingAs($admin)->post(route('admin.national-holidays.store'), [
            'name' => 'Hari Inovasi Sekolah',
            'date' => '2026-11-30',
            'category' => NationalHoliday::CATEGORY_PERINGATAN_NASIONAL,
            'is_holiday' => 0,
            'source' => 'SK Kepala Sekolah',
            'description' => 'Pameran karya siswa.',
        ]);

        $storeResponse->assertRedirect(route('admin.national-holidays.index'));
        $this->assertDatabaseHas('national_holidays', [
            'name' => 'Hari Inovasi Sekolah',
            'year' => 2026,
            'category' => NationalHoliday::CATEGORY_PERINGATAN_NASIONAL,
        ]);

        $holiday = NationalHoliday::where('name', 'Hari Inovasi Sekolah')->first();

        // Update
        $updateResponse = $this->actingAs($admin)->put(route('admin.national-holidays.update', $holiday), [
            'name' => 'Hari Inovasi Sekolah Terbuka',
            'date' => '2026-11-30',
            'category' => NationalHoliday::CATEGORY_PERINGATAN_NASIONAL,
            'is_holiday' => 0,
            'source' => 'SK Kepala Sekolah Revisi',
            'description' => 'Pameran karya seluruh siswa terbuka untuk wali murid.',
        ]);

        $updateResponse->assertRedirect(route('admin.national-holidays.index'));
        $this->assertDatabaseHas('national_holidays', [
            'id' => $holiday->id,
            'name' => 'Hari Inovasi Sekolah Terbuka',
        ]);

        // Delete
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.national-holidays.destroy', $holiday));
        $deleteResponse->assertRedirect(route('admin.national-holidays.index'));
        $this->assertDatabaseMissing('national_holidays', [
            'id' => $holiday->id,
        ]);
    }

    public function test_non_admin_cannot_access_national_holiday_management(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);

        $response = $this->actingAs($guru)->get(route('admin.national-holidays.index'));
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
    }
}
