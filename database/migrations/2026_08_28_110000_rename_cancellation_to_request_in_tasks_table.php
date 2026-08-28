<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Поля отмены обобщаются: в операции хранится один активный запрос —
     * на отмену или на правку (тип в request_type).
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('cancel_status', 'request_status');
            $table->renameColumn('cancel_reason', 'request_reason');
            $table->renameColumn('cancel_requested_by', 'request_requested_by');
            $table->renameColumn('cancel_requested_at', 'request_requested_at');
            $table->renameColumn('cancel_processed_by', 'request_processed_by');
            $table->renameColumn('cancel_processed_at', 'request_processed_at');
            $table->renameColumn('cancel_decision_comment', 'request_decision_comment');
        });

        Schema::table('tasks', function (Blueprint $table) {
            // cancel | edit
            $table->string('request_type')->nullable()->index()->after('status');
            // {field: {old: ..., new: ...}} — для показа «было → стало»
            $table->json('request_changes')->nullable()->after('request_reason');
            // валидированные данные правки, применяются при одобрении
            $table->json('request_payload')->nullable()->after('request_changes');
        });

        // Существующие запросы — все на отмену; статус cancelled → approved
        DB::table('tasks')->whereNotNull('request_status')->update(['request_type' => 'cancel']);
        DB::table('tasks')->where('request_status', 'cancelled')->update(['request_status' => 'approved']);
    }

    public function down(): void
    {
        DB::table('tasks')->where('request_status', 'approved')->update(['request_status' => 'cancelled']);

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['request_type']);
            $table->dropColumn(['request_type', 'request_changes', 'request_payload']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('request_status', 'cancel_status');
            $table->renameColumn('request_reason', 'cancel_reason');
            $table->renameColumn('request_requested_by', 'cancel_requested_by');
            $table->renameColumn('request_requested_at', 'cancel_requested_at');
            $table->renameColumn('request_processed_by', 'cancel_processed_by');
            $table->renameColumn('request_processed_at', 'cancel_processed_at');
            $table->renameColumn('request_decision_comment', 'cancel_decision_comment');
        });
    }
};
