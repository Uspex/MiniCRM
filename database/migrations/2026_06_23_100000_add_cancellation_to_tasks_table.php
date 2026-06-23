<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // null/active → requested → cancelled | rejected
            $table->string('cancel_status')->nullable()->index()->after('status');
            $table->text('cancel_reason')->nullable()->after('cancel_status');
            $table->unsignedBigInteger('cancel_requested_by')->nullable()->after('cancel_reason');
            $table->timestamp('cancel_requested_at')->nullable()->after('cancel_requested_by');
            $table->unsignedBigInteger('cancel_processed_by')->nullable()->after('cancel_requested_at');
            $table->timestamp('cancel_processed_at')->nullable()->after('cancel_processed_by');
            $table->text('cancel_decision_comment')->nullable()->after('cancel_processed_at');

            $table->foreign('cancel_requested_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('cancel_processed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['cancel_requested_by']);
            $table->dropForeign(['cancel_processed_by']);
            $table->dropColumn([
                'cancel_status',
                'cancel_reason',
                'cancel_requested_by',
                'cancel_requested_at',
                'cancel_processed_by',
                'cancel_processed_at',
                'cancel_decision_comment',
            ]);
        });
    }
};
