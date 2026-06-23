<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->index();
            $table->unsignedBigInteger('editor_id')->nullable()->index();
            $table->json('changes');
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_histories');
    }
};
