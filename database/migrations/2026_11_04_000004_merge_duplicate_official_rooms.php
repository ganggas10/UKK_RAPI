<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach ([
                'Laboratorium Komputer 1',
                'Laboratorium Komputer 2',
            ] as $roomName) {
                $roomIds = DB::table('rooms')
                    ->where('name', $roomName)
                    ->orderBy('id')
                    ->pluck('id');

                $canonicalId = $roomIds->first();
                if ($canonicalId === null) {
                    continue;
                }

                foreach ($roomIds->skip(1) as $duplicateId) {
                    DB::table('events')->where('room_id', $duplicateId)->update(['room_id' => $canonicalId]);
                    DB::table('complaints')->where('room_id', $duplicateId)->update(['room_id' => $canonicalId]);
                    DB::table('pic_assignments')->where('room_id', $duplicateId)->update(['room_id' => $canonicalId]);
                    DB::table('rooms')->where('id', $duplicateId)->delete();
                }
            }
        });
    }

    public function down(): void
    {
        // Merged room identities cannot be safely split without restoring historical context.
    }
};
