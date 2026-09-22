<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * audit_log sebelumnya hanya menautkan pic_id (kontak instansi) -- banyak
     * user login (terutama role internal Bappenas) tidak punya ref_pic sama
     * sekali, sehingga kolom "oleh" sering kosong. Ditambahkan user_id (tautan
     * langsung ke akun yang login) dan role (snapshot nama peran spatie/
     * laravel-permission SAAT aksi terjadi -- disimpan sebagai teks, bukan FK,
     * karena peran seorang user bisa berubah/dicabut di kemudian hari dan jejak
     * audit harus tetap mencerminkan peran yang berlaku ketika aksi dilakukan).
     * pic_id dipertahankan (existing-first) untuk kompatibilitas baris lama.
     */
    public function up(): void
    {
        Schema::table('audit_log', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('pic_id')->constrained('users')->nullOnDelete();
            $table->string('role', 100)->nullable()->after('user_id');
            $table->index('user_id', 'idx_audit_user');
        });
    }

    public function down(): void
    {
        Schema::table('audit_log', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('idx_audit_user');
            $table->dropColumn(['user_id', 'role']);
        });
    }
};
