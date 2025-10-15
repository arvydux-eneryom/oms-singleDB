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
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('sms_sid')->unique();
            $table->string('status');
            $table->string('to');
            $table->string('from');
            $table->string('body');
            $table->string('message_sid')->nullable();
            $table->string('account_sid');
            $table->enum('message_type', ['incoming', 'outgoing']);
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
