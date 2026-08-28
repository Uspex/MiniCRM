<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запрос и решение по нему — одна запись истории: событие меняется, решение дописывается.
     */
    public function up(): void
    {
        Schema::table('task_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('decided_by')->nullable()->after('comment');
            $table->timestamp('decided_at')->nullable()->after('decided_by');
            $table->text('decision_comment')->nullable()->after('decided_at');

            $table->foreign('decided_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('task_histories', function (Blueprint $table) {
            $table->dropForeign(['decided_by']);
            $table->dropColumn(['decided_by', 'decided_at', 'decision_comment']);
        });
    }
};
