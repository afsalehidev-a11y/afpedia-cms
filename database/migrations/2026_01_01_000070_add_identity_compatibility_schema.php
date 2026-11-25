<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('user_name', 256)->nullable()->after('name');
            $table->string('normalized_user_name', 256)->nullable()->unique()->after('user_name');
            $table->string('normalized_email', 256)->nullable()->index()->after('email');
            $table->boolean('email_confirmed')->default(false)->after('email_verified_at');
            $table->string('password_hash')->nullable()->after('password');
            $table->string('security_stamp', 36)->nullable()->after('password_hash');
            $table->string('concurrency_stamp', 36)->nullable()->after('security_stamp');
            $table->string('phone_number', 32)->nullable()->after('concurrency_stamp');
            $table->boolean('phone_number_confirmed')->default(false)->after('phone_number');
            $table->boolean('two_factor_enabled')->default(false)->after('phone_number_confirmed');
            $table->timestampTz('lockout_end')->nullable()->after('two_factor_enabled');
            $table->boolean('lockout_enabled')->default(true)->after('lockout_end');
            $table->unsignedInteger('access_failed_count')->default(0)->after('lockout_enabled');
            $table->timestampTz('created_at_utc')->nullable()->after('access_failed_count');
        });

        Schema::create('user_claims', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('user_id')->index();
            $table->string('claim_type', 256)->nullable();
            $table->text('claim_value')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('user_logins', function (Blueprint $table): void {
            $table->string('login_provider', 128);
            $table->string('provider_key', 128);
            $table->string('provider_display_name', 256)->nullable();
            $table->uuid('user_id')->index();
            $table->primary(['login_provider', 'provider_key']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('role_claims', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('role_id')->index();
            $table->string('claim_type', 256)->nullable();
            $table->text('claim_value')->nullable();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        Schema::create('user_tokens', function (Blueprint $table): void {
            $table->uuid('user_id');
            $table->string('login_provider', 128);
            $table->string('name', 128);
            $table->text('value')->nullable();
            $table->primary(['user_id', 'login_provider', 'name']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tokens');
        Schema::dropIfExists('role_claims');
        Schema::dropIfExists('user_logins');
        Schema::dropIfExists('user_claims');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['normalized_user_name']);
            $table->dropIndex(['normalized_email']);
            $table->dropColumn([
                'user_name', 'normalized_user_name', 'normalized_email',
                'email_confirmed', 'password_hash', 'security_stamp',
                'concurrency_stamp', 'phone_number', 'phone_number_confirmed',
                'two_factor_enabled', 'lockout_end', 'lockout_enabled',
                'access_failed_count', 'created_at_utc',
            ]);
        });
    }
};
