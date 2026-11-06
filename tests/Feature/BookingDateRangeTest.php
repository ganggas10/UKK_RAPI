<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingDateRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_submit_single_day_booking(): void
    {
        $user = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Aula', 'capacity' => 100]);

        $response = $this->actingAs($user)->post(route('guru.events.store'), $this->bookingPayload($room, '2026-11-10', '2026-11-10'));

        $response->assertRedirect(route('guru.events.index'));
        $event = Event::firstOrFail();
        $this->assertSame('2026-11-10', $event->bookingStartDate()->toDateString());
        $this->assertSame('2026-11-10', $event->bookingEndDate()->toDateString());
    }

    public function test_guru_can_submit_multi_day_booking_and_duration_is_inclusive(): void
    {
        $user = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Aula', 'capacity' => 100]);

        $this->actingAs($user)->post(route('guru.events.store'), $this->bookingPayload($room, '2026-11-10', '2026-11-13'));

        $event = Event::firstOrFail();
        $this->assertSame(4, $event->bookingDurationInDays());
    }

    public function test_booking_is_rejected_when_conflict_is_on_middle_or_last_day(): void
    {
        $user = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Aula', 'capacity' => 100]);
        Event::create($this->eventAttributes($room, '2026-11-10', '2026-11-13'));

        foreach ([['2026-11-12', '2026-11-12'], ['2026-11-13', '2026-11-13']] as [$start, $end]) {
            $this->actingAs($user)
                ->post(route('guru.events.store'), $this->bookingPayload($room, $start, $end))
                ->assertSessionHas('conflict_error');
        }
    }

    public function test_booking_is_rejected_when_end_date_precedes_start_date(): void
    {
        $user = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Aula', 'capacity' => 100]);

        $this->actingAs($user)
            ->post(route('guru.events.store'), $this->bookingPayload($room, '2026-11-13', '2026-11-10'))
            ->assertSessionHasErrors('end_date');
    }

    public function test_rejected_booking_does_not_block_another_booking(): void
    {
        $user = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Aula', 'capacity' => 100]);
        Event::create($this->eventAttributes($room, '2026-11-10', '2026-11-13', 'rejected'));

        $this->actingAs($user)
            ->post(route('guru.events.store'), $this->bookingPayload($room, '2026-11-12', '2026-11-12'))
            ->assertRedirect(route('guru.events.index'));
    }

    public function test_admin_rechecks_conflict_before_approving(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = Room::create(['name' => 'Aula', 'capacity' => 100]);
        Event::create($this->eventAttributes($room, '2026-11-10', '2026-11-13', 'approved'));
        $pending = Event::create($this->eventAttributes($room, '2026-11-12', '2026-11-12', 'pending'));

        $this->actingAs($admin)
            ->patch(route('admin.events.update', $pending), ['status' => 'approved'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $pending->refresh()->status);
    }

    public function test_api_accepts_multi_day_booking_and_calendar_returns_one_range_event(): void
    {
        $user = User::factory()->create(['role' => 'organisasi']);
        $room = Room::create(['name' => 'Aula', 'capacity' => 100]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/events', $this->bookingPayload($room, '2026-11-10', '2026-11-13'))
            ->assertCreated()
            ->assertJsonPath('data.start_date', '2026-11-10')
            ->assertJsonPath('data.end_date', '2026-11-13')
            ->assertJsonPath('data.duration_days', 4);

        Event::query()->update(['status' => 'approved']);

        $this->getJson(route('calendar.events'))
            ->assertOk()
            ->assertJsonPath('0.start', '2026-11-10T08:00')
            ->assertJsonPath('0.end', '2026-11-14T11:00');
    }

    private function bookingPayload(Room $room, string $startDate, string $endDate): array
    {
        return [
            'title' => 'Kegiatan Sekolah',
            'description' => 'Pengujian peminjaman ruang',
            'room_id' => $room->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_session' => 'Sesi 2',
            'end_session' => 'Sesi 5',
        ];
    }

    private function eventAttributes(Room $room, string $startDate, string $endDate, string $status = 'approved'): array
    {
        return [
            'title' => 'Agenda Existing',
            'description' => null,
            'room_id' => $room->id,
            'user_id' => User::factory()->create()->id,
            'date' => $startDate,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => '08:00',
            'end_time' => '11:00',
            'session' => 'Sesi 2 - Sesi 5',
            'status' => $status,
        ];
    }
}
