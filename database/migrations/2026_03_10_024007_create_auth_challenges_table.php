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
        /**
         * Stores verification challenges for both server-generated OTPs
         * and externally generated one-time codes such as authenticator apps.
         */
        Schema::create('auth_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('challenge_token')->unique();

            $table->string('code')->nullable();

            $table->string('method')
                ->comment('Verification mechanism used for this challenge (otp_email, otp_sms, totp, passkey, push, etc).');

            $table->string('context')
                ->comment('Purpose of the challenge (login, email_verification, password_reset, disable_two_fa, etc).');

            $table->unsignedTinyInteger('attempts')
                ->default(0)
                ->comment('Number of failed verification attempts for this challenge.');

            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'context']);
            $table->index(['user_id', 'method']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_challenges');
    }
};