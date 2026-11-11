<?php

namespace Database\Seeders;

use App\Models\SchoolSession;
use Illuminate\Database\Seeder;

class SchoolSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sessions = [
            ['name' => 'Sesi 1', 'start_time' => '07:15', 'end_time' => '08:00', 'sort_order' => 1],
            ['name' => 'Sesi 2', 'start_time' => '08:00', 'end_time' => '08:45', 'sort_order' => 2],
            ['name' => 'Sesi 3', 'start_time' => '08:45', 'end_time' => '09:30', 'sort_order' => 3],
            ['name' => 'Sesi 4', 'start_time' => '09:30', 'end_time' => '10:15', 'sort_order' => 4],
            ['name' => 'Sesi 5', 'start_time' => '10:15', 'end_time' => '11:00', 'sort_order' => 5],
            ['name' => 'Sesi 6', 'start_time' => '11:00', 'end_time' => '11:45', 'sort_order' => 6],
            ['name' => 'Sesi 7', 'start_time' => '11:45', 'end_time' => '12:30', 'sort_order' => 7],
            ['name' => 'Sesi 8', 'start_time' => '12:30', 'end_time' => '13:15', 'sort_order' => 8],
            ['name' => 'Sesi 9', 'start_time' => '13:15', 'end_time' => '14:00', 'sort_order' => 9],
            ['name' => 'Sesi 10', 'start_time' => '14:00', 'end_time' => '14:45', 'sort_order' => 10],
            ['name' => 'Sesi 11', 'start_time' => '14:45', 'end_time' => '15:30', 'sort_order' => 11],
        ];

        foreach ($sessions as $session) {
            SchoolSession::updateOrCreate(
                ['name' => $session['name']],
                [
                    'start_time' => $session['start_time'],
                    'end_time' => $session['end_time'],
                    'sort_order' => $session['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
