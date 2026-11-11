<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('national_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->unsignedSmallInteger('year')->index();
            $table->string('category', 50)->index(); // libur_nasional, keagamaan_islam, keagamaan_kristen, keagamaan_hindu, keagamaan_buddha, keagamaan_konghucu, peringatan_nasional, cuti_bersama
            $table->string('color', 20);
            $table->text('description')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->string('source')->nullable();
            $table->timestamps();

            $table->unique(['date', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('national_holidays');
    }
};
