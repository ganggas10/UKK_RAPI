<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('room_move_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('old_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('new_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('old_room_name');
            $table->string('new_room_name');
            $table->string('target_phone')->nullable();
            $table->text('move_reason');
            $table->text('wa_message')->nullable();
            $table->enum('wa_status', ['belum_dikirim', 'berhasil_dikirim', 'gagal_dikirim'])->default('belum_dikirim');
            $table->timestamp('wa_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_move_histories');
    }
};
