<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $legacyNames = [
                'Aula Utama' => 'Aula Atas',
                'Ruang Rapat' => 'Ruang Wijaya',
                'Ruang UKS' => 'Laboratorium IPA',
            ];

            foreach ($legacyNames as $legacyName => $officialName) {
                DB::table('rooms')->where('name', $legacyName)->update([
                    'name' => $officialName,
                    'updated_at' => now(),
                ]);
            }

            foreach ($this->officialRooms() as $room) {
                $existingRoom = DB::table('rooms')->where('name', $room['name'])->first();

                if ($existingRoom !== null) {
                    DB::table('rooms')->where('id', $existingRoom->id)->update([
                        'location' => $room['location'],
                        'capacity' => $room['capacity'],
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('rooms')->insert([
                        ...$room,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        // Room records and their historical relationships are intentionally preserved.
    }

    /**
     * @return list<array{name: string, location: string, capacity: int}>
     */
    private function officialRooms(): array
    {
        $rooms = [];

        for ($number = 1; $number <= 35; $number++) {
            $rooms[] = [
                'name' => "Ruang {$number}",
                'location' => 'Ruang Kelas',
                'capacity' => 40,
            ];
        }

        for ($number = 1; $number <= 22; $number++) {
            $rooms[] = [
                'name' => "Laboratorium Komputer {$number}",
                'location' => 'Laboratorium Komputer',
                'capacity' => 40,
            ];
        }

        foreach (['Laboratorium IPA', 'Laboratorium Penataan Barang', 'Masjid', 'Ruang Wijaya'] as $name) {
            $rooms[] = [
                'name' => $name,
                'location' => 'Laboratorium dan Ruangan Khusus',
                'capacity' => 40,
            ];
        }

        foreach (['Lapangan Basket', 'Lapangan Volly', 'Aula Atas', 'Aula Bawah'] as $name) {
            $rooms[] = [
                'name' => $name,
                'location' => 'Lapangan dan Aula',
                'capacity' => 100,
            ];
        }

        return $rooms;
    }
};
