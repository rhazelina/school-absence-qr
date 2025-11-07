<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('qr_codes', 'jadwal_id')) {
                $table->foreignId('jadwal_id')
                    ->nullable()
                    ->after('guru_id')
                    ->constrained('jadwal_pembelajaran')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('qr_codes', 'waktu_pertemuan')) {
                $table->timestamp('waktu_pertemuan')
                    ->nullable()
                    ->after('jadwal_id');
            }

            if (!Schema::hasColumn('qr_codes', 'is_active')) {
                $table->boolean('is_active')
                    ->default(true)
                    ->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            if (Schema::hasColumn('qr_codes', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('qr_codes', 'waktu_pertemuan')) {
                $table->dropColumn('waktu_pertemuan');
            }

            if (Schema::hasColumn('qr_codes', 'jadwal_id')) {
                $table->dropForeign(['jadwal_id']);
                $table->dropColumn('jadwal_id');
            }
        });
    }
};
