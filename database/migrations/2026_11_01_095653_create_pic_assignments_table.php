<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pic_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->string('status')->default('aktif');
            $table->text('notes')->nullable();
            $table->text('replacement_reason')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('rooms')->whereNotNull('pic_id')->orderBy('id')->eachById(function (object $room): void {
            DB::table('pic_assignments')->insert([
                'room_id' => $room->id,
                'pic_id' => $room->pic_id,
                'starts_at' => now()->toDateString(),
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pic_assignments');
    }
};
