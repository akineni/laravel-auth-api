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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('avatar')->nullable();
            
            $table->enum('gender', [
                'male',
                'female',
                'other',
                'prefer_not_to_say'
            ])->nullable();
            $table->string('phone_number')->nullable();
            $table->string('postcode')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->mediumText('address')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('signup_source')
                ->default('self')
                ->comment('Account origin: self, admin, seeder, google, facebook, etc');
            $table->string('password');
            $table->timestamp('last_login')->nullable();

            // Lifecycle status
            $table->enum('status', ['pending', 'active', 'inactive', 'suspended'])
                ->default('pending')
                ->comment('User lifecycle status');

            // Booleans for additional control
            $table->boolean('two_fa')->default(false)->comment('Whether 2FA is enabled');
            $table->string('two_fa_secret')->nullable();

            $table->unsignedInteger('failed_logins')->default(0);
            $table->timestamp('locked_until')->nullable();

            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
