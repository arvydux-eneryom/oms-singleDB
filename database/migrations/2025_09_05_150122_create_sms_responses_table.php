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
        Schema::create('sms_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('sms_questions')->cascadeOnDelete();
            $table->string('phone');
            $table->string('answer'); // store "1", "2", "3", or "4"
            $table->string('plain_answer')->nullable(); // e.g. "Basic"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_responses');
    }
};
