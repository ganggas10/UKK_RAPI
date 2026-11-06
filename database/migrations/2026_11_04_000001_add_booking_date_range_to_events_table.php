<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->date('start_date')->nullable()->after('date');
            $table->date('end_date')->nullable()->after('start_date');
            $table->index(['room_id', 'start_date', 'end_date']);
        });

        DB::table('events')->whereNull('start_date')->update([
            'start_date' => DB::raw('date'),
            'end_date' => DB::raw('date'),
        ]);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropIndex('events_room_id_start_date_end_date_index');
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
