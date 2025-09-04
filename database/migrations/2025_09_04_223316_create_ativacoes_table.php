<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ativacoes', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint')->unique();
            $table->date('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('ativacoes');
    }
};
