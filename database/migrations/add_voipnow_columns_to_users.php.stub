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
            $table->string('voipnow_access_token')->nullable();
            $table->integer('voipnow_token_expires_in')->nullable();
            $table->timestamp('voipnow_token_expired_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('voipnow_access_token');
            $table->dropColumn('voipnow_token_expires_in');
            $table->dropColumn('voipnow_token_expired_at');
        });
    }
};
