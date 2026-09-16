<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_klaster', function (Blueprint $table) {
            $table->id();
            $table->string('nama_klaster', 150);
            $table->unique('nama_klaster', 'uq_klaster_nama');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Master klaster PSN sesuai kolom Klaster pada Matrik Sandingan');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ref_klaster');
    }
};
