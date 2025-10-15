<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sms_responses', function (Blueprint $table) {
            $table->foreignId('sent_sms_question_id')
                ->nullable()
                ->after('question_id')
                ->constrained('sent_sms_questions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_responses', function (Blueprint $table) {
            $table->dropForeign(['sent_sms_question_id']);
            $table->dropColumn('sent_sms_question_id');
        });
    }
};
