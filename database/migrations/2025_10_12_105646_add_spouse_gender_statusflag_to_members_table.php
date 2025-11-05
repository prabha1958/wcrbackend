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
        Schema::table('members', function (Blueprint $table) {
            // Add new fields near the end of the table
            $table->string('spouse_name')->nullable()->after('wedding_date');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('spouse_name');
            $table->boolean('status_flag')->default(true)->after('gender');
            // true = active member, false = not active / deceased / moved out
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['spouse_name', 'gender', 'status_flag']);
        });
    }
};
