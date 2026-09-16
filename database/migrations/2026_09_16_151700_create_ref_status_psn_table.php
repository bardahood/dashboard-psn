<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_status_psn', function (Blueprint $table) {
            $table->id();
            $table->string('nama_status', 50);
            $table->integer('urutan')->default(0);
            $table->unique('nama_status', 'uq_status_psn_nama');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->comment('Master status lifecycle PSN (Gambaran Umum -- kolom Status PSN pada Project Profile)');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ref_status_psn');
    }
};
