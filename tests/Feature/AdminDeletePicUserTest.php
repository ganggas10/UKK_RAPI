<?php

namespace Tests\Feature;

use App\Models\PicAssignment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeletePicUserTest extends TestCase
{
    use RefreshDatabase;

    private const ASSIGNED_ERROR = 'Tidak dapat menghapus pengguna ini karena masih bertanggung jawab atas satu atau lebih ruangan. Silakan pindahkan atau hapus penugasan PIC Ruang terlebih dahulu.';

    public function test_admin_cannot_delete_pic_with_active_assignment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pic = User::factory()->create(['role' => 'pic']);
        $room = Room::create(['name' => 'Lab 1', 'capacity' => 30]);
        $assignment = PicAssignment::create([
            'room_id' => $room->id,
            'pic_id' => $pic->id,
            'starts_at' => now()->toDateString(),
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $pic))
            ->assertSessionHas('error', self::ASSIGNED_ERROR);

        $this->assertDatabaseHas('users', ['id' => $pic->id]);
        $this->assertDatabaseHas('pic_assignments', ['id' => $assignment->id, 'pic_id' => $pic->id, 'status' => 'aktif']);
    }

    public function test_admin_cannot_delete_pic_set_as_main_room_pic(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pic = User::factory()->create(['role' => 'pic']);
        $room = Room::create(['name' => 'Ruang Wijaya', 'capacity' => 30, 'pic_id' => $pic->id]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $pic))
            ->assertSessionHas('error', self::ASSIGNED_ERROR);

        $this->assertDatabaseHas('users', ['id' => $pic->id]);
        $this->assertSame($pic->id, $room->refresh()->pic_id);
    }

    public function test_admin_can_delete_pic_whose_assignments_have_ended(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pic = User::factory()->create(['role' => 'pic']);
        $room = Room::create(['name' => 'Lab 2', 'capacity' => 30]);
        PicAssignment::create([
            'room_id' => $room->id,
            'pic_id' => $pic->id,
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->toDateString(),
            'status' => 'selesai',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $pic))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success', 'Pengguna PIC Ruang berhasil dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $pic->id]);
        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_admin_can_still_delete_non_pic_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guru = User::factory()->create(['role' => 'guru']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $guru))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success', 'User berhasil dihapus!');

        $this->assertDatabaseMissing('users', ['id' => $guru->id]);
    }
}

