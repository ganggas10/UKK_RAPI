<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('session')->nullable()->after('date');
            $table->string('rejection_message')->nullable()->after('status');
            $table->text('rejection_notes')->nullable()->after('rejection_message');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['session', 'rejection_message', 'rejection_notes']);
        });
    }
};
