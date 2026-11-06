<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaHistoryPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_agenda_history_shows_five_records_per_page_with_continuous_numbering(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = Room::create(['name' => 'Ruang 1', 'location' => 'Ruang Kelas', 'capacity' => 40]);
        $applicant = User::factory()->create(['role' => 'guru']);

        foreach (range(1, 8) as $number) {
            Event::create([
                'title' => "Agenda {$number}",
                'room_id' => $room->id,
                'user_id' => $applicant->id,
                'date' => '2026-11-10',
                'start_date' => '2026-11-10',
                'end_date' => '2026-11-10',
                'start_time' => '08:00',
                'end_time' => '08:45',
                'session' => 'Sesi 2',
                'status' => 'approved',
            ]);
        }

        $pageOne = $this->actingAs($admin)->get(route('waka.agenda.history', [
            'status' => 'approved',
        ]));
        $pageOne->assertOk()
            ->assertSee('Menampilkan 1–5 dari 8 agenda', false)
            ->assertSee('<td>1</td>', false)
            ->assertSee('<td>5</td>', false)
            ->assertSee('status=approved', false);
        $this->assertSame(5, substr_count($pageOne->getContent(), '<strong>Agenda '));

        $pageTwo = $this->actingAs($admin)->get(route('waka.agenda.history', [
            'status' => 'approved',
            'page' => 2,
        ]));
        $pageTwo->assertOk()
            ->assertSee('Menampilkan 6–8 dari 8 agenda', false)
            ->assertSee('<td>6</td>', false)
            ->assertSee('<td>8</td>', false);
        $this->assertSame(3, substr_count($pageTwo->getContent(), '<strong>Agenda '));
    }

    public function test_agenda_history_hides_pagination_for_five_or_fewer_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = Room::create(['name' => 'Ruang 1', 'location' => 'Ruang Kelas', 'capacity' => 40]);
        $applicant = User::factory()->create(['role' => 'guru']);

        foreach (range(1, 5) as $number) {
            Event::create([
                'title' => "Agenda {$number}",
                'room_id' => $room->id,
                'user_id' => $applicant->id,
                'date' => '2026-11-10',
                'start_date' => '2026-11-10',
                'end_date' => '2026-11-10',
                'start_time' => '08:00',
                'end_time' => '08:45',
                'session' => 'Sesi 2',
                'status' => 'approved',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('waka.agenda.history'))
            ->assertOk()
            ->assertDontSee('Menampilkan 1–5 dari 5 agenda', false)
            ->assertDontSee('page=2', false);
    }
}
