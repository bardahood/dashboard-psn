<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjembatani akun login (Laravel Breeze) dengan direktori personil (ref_pic)
     * dan hak akses (hak_akses.level_akses/is_active) pada skema PSN.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('pic_id')->nullable()->after('id');
            $table->foreign('pic_id')->references('id')->on('ref_pic')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pic_id']);
            $table->dropColumn('pic_id');
        });
    }
};
