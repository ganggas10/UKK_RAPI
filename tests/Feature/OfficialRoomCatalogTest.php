<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialRoomCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_official_room_catalog_contains_exactly_65_unique_rooms(): void
    {
        $this->seed();

        $this->assertSame(65, Room::count());
        $this->assertSame(65, Room::query()->distinct('name')->count('name'));
        $this->assertSame(35, Room::where('location', 'Ruang Kelas')->count());
        $this->assertSame(22, Room::where('location', 'Laboratorium Komputer')->count());
        $this->assertSame(4, Room::where('location', 'Laboratorium dan Ruangan Khusus')->count());
        $this->assertSame(4, Room::where('location', 'Lapangan dan Aula')->count());
    }

    public function test_room_names_are_canonical_and_legacy_names_are_absent(): void
    {
        $this->seed();

        $this->assertDatabaseMissing('rooms', ['name' => 'Aula Utama']);
        $this->assertDatabaseMissing('rooms', ['name' => 'Ruang Rapat']);
        $this->assertDatabaseMissing('rooms', ['name' => 'Ruang UKS']);
        $this->assertDatabaseMissing('rooms', ['name' => 'Lab Komputer 1']);
        $this->assertDatabaseMissing('rooms', ['name' => 'Lab Komputer 2']);
        $this->assertDatabaseHas('rooms', ['name' => 'Laboratorium Komputer 22']);
        $this->assertDatabaseHas('rooms', ['name' => 'Aula Bawah']);
    }
}
