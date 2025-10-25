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
        // Customers table indexes
        Schema::table('customers', function (Blueprint $table) {
            $table->index('tenant_id', 'idx_customers_tenant_id');
            $table->index(['tenant_id', 'status'], 'idx_customers_tenant_status');
            $table->index(['tenant_id', 'created_at'], 'idx_customers_tenant_created');
            $table->index('company', 'idx_customers_company');
        });

        // Customer emails table indexes
        Schema::table('customer_emails', function (Blueprint $table) {
            $table->index('customer_id', 'idx_customer_emails_customer_id');
            $table->index(['customer_id', 'is_verified'], 'idx_customer_emails_customer_verified');
        });

        // Customer phones table indexes
        Schema::table('customer_phones', function (Blueprint $table) {
            $table->index('customer_id', 'idx_customer_phones_customer_id');
            $table->index('phone', 'idx_customer_phones_phone');
            $table->index(['customer_id', 'is_sms_enabled'], 'idx_customer_phones_customer_sms');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('system_id', 'idx_users_system_id');
            $table->index(['system_id', 'is_tenant'], 'idx_users_system_tenant');
            $table->index('created_at', 'idx_users_created_at');
        });

        // Domains table indexes
        Schema::table('domains', function (Blueprint $table) {
            $table->index('system_id', 'idx_domains_system_id');
            $table->index('tenant_id', 'idx_domains_tenant_id');
            $table->index('domain', 'idx_domains_domain');
        });

        // SMS messages table indexes
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->index('sms_sid', 'idx_sms_messages_sid');
            $table->index(['message_type', 'created_at'], 'idx_sms_messages_type_created');
            $table->index(['to', 'message_type'], 'idx_sms_messages_to_type');
            $table->index('user_id', 'idx_sms_messages_user_id');
        });

        // Sent SMS questions table indexes
        Schema::table('sent_sms_questions', function (Blueprint $table) {
            $table->index('to', 'idx_sent_sms_questions_to');
            $table->index(['to', 'created_at'], 'idx_sent_sms_questions_to_created');
            $table->index('sms_question_id', 'idx_sent_sms_questions_question_id');
            $table->index('user_id', 'idx_sent_sms_questions_user_id');
        });

        // SMS responses table indexes
        Schema::table('sms_responses', function (Blueprint $table) {
            $table->index('phone', 'idx_sms_responses_phone');
            $table->index('question_id', 'idx_sms_responses_question_id');
            $table->index('sent_sms_question_id', 'idx_sms_responses_sent_question_id');
        });

        // Telegram sessions table indexes
        Schema::table('telegram_sessions', function (Blueprint $table) {
            $table->index('user_id', 'idx_telegram_sessions_user_id');
            $table->index('identifier', 'idx_telegram_sessions_identifier');
            $table->index(['user_id', 'is_active'], 'idx_telegram_sessions_user_active');
            $table->index(['user_id', 'expires_at'], 'idx_telegram_sessions_user_expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop customers indexes
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_tenant_id');
            $table->dropIndex('idx_customers_tenant_status');
            $table->dropIndex('idx_customers_tenant_created');
            $table->dropIndex('idx_customers_company');
        });

        // Drop customer emails indexes
        Schema::table('customer_emails', function (Blueprint $table) {
            $table->dropIndex('idx_customer_emails_customer_id');
            $table->dropIndex('idx_customer_emails_customer_verified');
        });

        // Drop customer phones indexes
        Schema::table('customer_phones', function (Blueprint $table) {
            $table->dropIndex('idx_customer_phones_customer_id');
            $table->dropIndex('idx_customer_phones_phone');
            $table->dropIndex('idx_customer_phones_customer_sms');
        });

        // Drop users indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_system_id');
            $table->dropIndex('idx_users_system_tenant');
            $table->dropIndex('idx_users_created_at');
        });

        // Drop domains indexes
        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex('idx_domains_system_id');
            $table->dropIndex('idx_domains_tenant_id');
            $table->dropIndex('idx_domains_domain');
        });

        // Drop SMS messages indexes
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropIndex('idx_sms_messages_sid');
            $table->dropIndex('idx_sms_messages_type_created');
            $table->dropIndex('idx_sms_messages_to_type');
            $table->dropIndex('idx_sms_messages_user_id');
        });

        // Drop sent SMS questions indexes
        Schema::table('sent_sms_questions', function (Blueprint $table) {
            $table->dropIndex('idx_sent_sms_questions_to');
            $table->dropIndex('idx_sent_sms_questions_to_created');
            $table->dropIndex('idx_sent_sms_questions_question_id');
            $table->dropIndex('idx_sent_sms_questions_user_id');
        });

        // Drop SMS responses indexes
        Schema::table('sms_responses', function (Blueprint $table) {
            $table->dropIndex('idx_sms_responses_phone');
            $table->dropIndex('idx_sms_responses_question_id');
            $table->dropIndex('idx_sms_responses_sent_question_id');
        });

        // Drop Telegram sessions indexes
        Schema::table('telegram_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_telegram_sessions_user_id');
            $table->dropIndex('idx_telegram_sessions_identifier');
            $table->dropIndex('idx_telegram_sessions_user_active');
            $table->dropIndex('idx_telegram_sessions_user_expires');
        });
    }
};
