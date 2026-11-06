<?php

namespace Tests\Feature;

use App\Models\PicAssignment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PicAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_room_only_to_pic_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pic = User::factory()->create(['role' => 'pic']);
        $guru = User::factory()->create(['role' => 'guru']);
        $room = Room::create(['name' => 'Lab A', 'location' => 'Gedung A', 'capacity' => 20]);

        $this->actingAs($admin)->post(route('admin.pic-assignments.store'), [
            'room_id' => $room->id,
            'pic_id' => $guru->id,
            'starts_at' => now()->toDateString(),
        ])->assertSessionHasErrors('pic_id');

        $this->actingAs($admin)->post(route('admin.pic-assignments.store'), [
            'room_id' => $room->id,
            'pic_id' => $pic->id,
            'starts_at' => now()->toDateString(),
        ])->assertRedirect(route('admin.pic-assignments.index'));

        $this->assertSame($pic->id, $room->refresh()->pic_id);
        $this->assertDatabaseHas('pic_assignments', ['room_id' => $room->id, 'pic_id' => $pic->id, 'status' => 'aktif']);
    }

    public function test_replacing_pic_preserves_history_and_updates_active_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $oldPic = User::factory()->create(['role' => 'pic']);
        $newPic = User::factory()->create(['role' => 'pic']);
        $room = Room::create(['name' => 'Lab B', 'location' => 'Gedung B', 'capacity' => 20, 'pic_id' => $oldPic->id]);
        $assignment = PicAssignment::create([
            'room_id' => $room->id,
            'pic_id' => $oldPic->id,
            'starts_at' => now()->subDay()->toDateString(),
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)->put(route('admin.pic-assignments.update', $assignment), [
            'pic_id' => $newPic->id,
            'starts_at' => now()->toDateString(),
            'replacement_reason' => 'Rotasi tugas',
        ])->assertRedirect(route('admin.pic-assignments.index'));

        $this->assertSame($newPic->id, $room->refresh()->pic_id);
        $this->assertDatabaseHas('pic_assignments', ['id' => $assignment->id, 'status' => 'selesai', 'replacement_reason' => 'Rotasi tugas']);
        $this->assertDatabaseHas('pic_assignments', ['room_id' => $room->id, 'pic_id' => $newPic->id, 'status' => 'aktif']);
    }

    public function test_pic_cannot_update_complaint_for_another_pic_room(): void
    {
        $pic = User::factory()->create(['role' => 'pic']);
        $otherPic = User::factory()->create(['role' => 'pic']);
        $room = Room::create(['name' => 'Lab C', 'capacity' => 20, 'pic_id' => $otherPic->id]);
        $complaint = $room->complaints()->create([
            'reporter_id' => User::factory()->create(['role' => 'guru'])->id,
            'description' => 'Proyektor tidak menyala',
        ]);

        $this->actingAs($pic)->patch(route('pic.complaints.status', $complaint), [
            'status_repair' => 'Sudah Diperbaiki',
        ])->assertForbidden();
    }
}
