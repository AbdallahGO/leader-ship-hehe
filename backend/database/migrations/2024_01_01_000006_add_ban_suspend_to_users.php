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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('role');
            $table->boolean('is_suspended')->default(false)->after('is_banned');
            $table->timestamp('banned_at')->nullable()->after('is_suspended');
            $table->timestamp('suspended_at')->nullable()->after('banned_at');

            // Add indexes for performance
            $table->index('is_banned');
            $table->index('is_suspended');
            $table->index('banned_at');
            $table->index('suspended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_banned']);
            $table->dropIndex(['is_suspended']);
            $table->dropIndex(['banned_at']);
            $table->dropIndex(['suspended_at']);

            $table->dropColumn([
                'is_banned',
                'is_suspended',
                'banned_at',
                'suspended_at',
            ]);
        });
    }
};
