<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * История ведётся по всем взаимодействиям с операцией, а не только по прямым правкам.
     */
    public function up(): void
    {
        Schema::table('task_histories', function (Blueprint $table) {
            $table->string('event')->default('updated')->after('editor_id');
            $table->text('comment')->nullable()->after('changes');
        });

        Schema::table('task_histories', function (Blueprint $table) {
            $table->json('changes')->nullable()->change();
        });

        DB::table('task_histories')->update(['event' => 'updated']);
    }

    public function down(): void
    {
        Schema::table('task_histories', function (Blueprint $table) {
            $table->dropColumn(['event', 'comment']);
        });
    }
};
