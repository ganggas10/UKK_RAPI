<?php

namespace Database\Seeders;

use App\Models\PicAssignment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ubah nama lama "Laboratorium Komputer 1..22" menjadi "Lab 1..22" jika sudah ada di DB
        for ($number = 1; $number <= 22; $number++) {
            $oldName = "Laboratorium Komputer {$number}";
            $newName = "Lab {$number}";

            $oldRoom = Room::where('name', $oldName)->first();
            $newRoom = Room::where('name', $newName)->first();

            if ($oldRoom && ! $newRoom) {
                $oldRoom->update([
                    'name' => $newName,
                    'location' => 'Laboratorium Komputer',
                    'capacity' => 40,
                ]);
            } elseif ($oldRoom && $newRoom) {
                // Jika keduanya ada, pindahkan relasi lalu hapus yang lama
                \DB::table('events')->where('room_id', $oldRoom->id)->update(['room_id' => $newRoom->id]);
                \DB::table('complaints')->where('room_id', $oldRoom->id)->update(['room_id' => $newRoom->id]);
                \DB::table('pic_assignments')->where('room_id', $oldRoom->id)->update(['room_id' => $newRoom->id]);
                $oldRoom->delete();
            }
        }

        // 2. Pastikan seluruh ruangan resmi tersedia
        foreach ($this->officialRooms() as $room) {
            Room::updateOrCreate(['name' => $room['name']], $room);
        }

        // 3. Sinkronkan penugasan PIC Ruang sesuai pembagian tugas resmi
        $this->seedPicAssignments();
    }

    /**
     * @return list<array{name: string, location: string, capacity: int}>
     */
    private function officialRooms(): array
    {
        $rooms = [];

        for ($number = 1; $number <= 35; $number++) {
            $rooms[] = ['name' => "Ruang {$number}", 'location' => 'Ruang Kelas', 'capacity' => 40];
        }

        for ($number = 1; $number <= 22; $number++) {
            $rooms[] = ['name' => "Lab {$number}", 'location' => 'Laboratorium Komputer', 'capacity' => 40];
        }

        foreach (['Laboratorium IPA', 'Laboratorium Penataan Barang', 'Masjid', 'Ruang Wijaya'] as $name) {
            $rooms[] = ['name' => $name, 'location' => 'Laboratorium dan Ruangan Khusus', 'capacity' => 40];
        }

        foreach (['Lapangan Basket', 'Lapangan Volly', 'Aula Atas', 'Aula Bawah'] as $name) {
            $rooms[] = ['name' => $name, 'location' => 'Lapangan dan Aula', 'capacity' => 100];
        }

        return $rooms;
    }

    private function seedPicAssignments(): void
    {
        $admin = User::where('role', 'admin')->first();

        $picRoomMap = [
            'lega@sekolah.com' => [
                'Lab 2', 'Lab 4', 'Lab 6', 'Lab 8', 'Lab 11', 'Lab 14', 'Lab 19', 'Lab 21', 'Lab 22',
            ],
            'wahid@sekolah.com' => [
                'Lab 3', 'Lab 7', 'Lab 9', 'Lab 16', 'Lab 17', 'Lab 20', 'Lab 21', 'Lab 22',
            ],
            'heri@sekolah.com' => [
                'Lab 1', 'Lab 5', 'Lab 7', 'Lab 10', 'Lab 12', 'Lab 13', 'Lab 18', 'Lab 21', 'Lab 22',
            ],
            'arif@sekolah.com' => [
                'Ruang Wijaya',
            ],
        ];

        foreach ($picRoomMap as $email => $roomNames) {
            $pic = User::where('email', $email)->first();
            if (! $pic) {
                continue;
            }

            foreach ($roomNames as $roomName) {
                $room = Room::where('name', $roomName)->first();
                if (! $room) {
                    continue;
                }

                // Set pic_id utama pada tabel rooms jika belum terisi (atau isi dengan PIC terkait)
                if (! $room->pic_id) {
                    $room->update(['pic_id' => $pic->id]);
                }

                // Buat atau perbarui PicAssignment aktif (mendukung multi-PIC untuk Lab 7, Lab 21, Lab 22)
                PicAssignment::updateOrCreate(
                    [
                        'room_id' => $room->id,
                        'pic_id' => $pic->id,
                        'status' => 'aktif',
                    ],
                    [
                        'starts_at' => now()->toDateString(),
                        'notes' => 'Penugasan resmi PIC Ruangan',
                        'assigned_by' => $admin?->id,
                    ]
                );
            }
        }
    }
}
