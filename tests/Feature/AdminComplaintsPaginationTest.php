<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminComplaintsPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_complaints_page_shows_at_most_five_reports_and_continuous_numbering(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = Room::create(['name' => 'Ruang 1', 'location' => 'Ruang Kelas', 'capacity' => 40]);
        $reporter = User::factory()->create(['role' => 'guru']);

        foreach (range(1, 8) as $number) {
            Complaint::create([
                'room_id' => $room->id,
                'reporter_id' => $reporter->id,
                'description' => "Laporan {$number}",
                'status_repair' => 'Belum Diperbaiki',
            ]);
        }

        $pageOne = $this->actingAs($admin)->get(route('admin.complaints.index', ['status' => 'open']));
        $pageOne->assertOk()
            ->assertSee('Menampilkan 1–5 dari 8 laporan', false)
            ->assertSee('<td>1</td>', false)
            ->assertSee('<td>5</td>', false);
        $this->assertSame(5, substr_count($pageOne->getContent(), 'class="complaint-description"'));

        $pageTwo = $this->actingAs($admin)->get(route('admin.complaints.index', ['status' => 'open', 'page' => 2]));
        $pageTwo->assertOk()
            ->assertSee('Menampilkan 6–8 dari 8 laporan', false)
            ->assertSee('<td>6</td>', false)
            ->assertSee('<td>8</td>', false);
        $this->assertSame(3, substr_count($pageTwo->getContent(), 'class="complaint-description"'));
    }

    public function test_pagination_is_hidden_when_five_or_fewer_reports_exist(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $room = Room::create(['name' => 'Ruang 1', 'location' => 'Ruang Kelas', 'capacity' => 40]);
        $reporter = User::factory()->create(['role' => 'guru']);

        foreach (range(1, 5) as $number) {
            Complaint::create([
                'room_id' => $room->id,
                'reporter_id' => $reporter->id,
                'description' => "Laporan {$number}",
                'status_repair' => 'Belum Diperbaiki',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.complaints.index'))
            ->assertOk()
            ->assertDontSee('Menampilkan 1–5 dari 5 laporan', false);
    }
}
