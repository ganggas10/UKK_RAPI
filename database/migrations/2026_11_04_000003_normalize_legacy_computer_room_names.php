<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach ([
                'Lab Komputer 1' => 'Laboratorium Komputer 1',
                'Lab Komputer 2' => 'Laboratorium Komputer 2',
            ] as $legacyName => $officialName) {
                DB::table('rooms')->where('name', $legacyName)->update([
                    'name' => $officialName,
                    'location' => 'Laboratorium Komputer',
                    'capacity' => 40,
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Keep official names and historical room relationships intact on rollback.
    }
};
