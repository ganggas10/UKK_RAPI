<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->officialRooms() as $room) {
            Room::updateOrCreate(['name' => $room['name']], $room);
        }
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
            $rooms[] = ['name' => "Laboratorium Komputer {$number}", 'location' => 'Laboratorium Komputer', 'capacity' => 40];
        }

        foreach (['Laboratorium IPA', 'Laboratorium Penataan Barang', 'Masjid', 'Ruang Wijaya'] as $name) {
            $rooms[] = ['name' => $name, 'location' => 'Laboratorium dan Ruangan Khusus', 'capacity' => 40];
        }

        foreach (['Lapangan Basket', 'Lapangan Volly', 'Aula Atas', 'Aula Bawah'] as $name) {
            $rooms[] = ['name' => $name, 'location' => 'Lapangan dan Aula', 'capacity' => 100];
        }

        return $rooms;
    }
}
