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
        Schema::create('members', function (Blueprint $table) {
            $table->bigIncrements('id'); // PK, bigint
            $table->string('family_name'); // required
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('mobile_number')->unique()->nullable();
            $table->text('residential_address')->nullable();
            $table->string('occupation')->nullable();
            $table->enum('status', ['in_service', 'retired', 'other'])->default('in_service');
            $table->string('profile_photo')->nullable(); // path to file
            $table->decimal('membership_fee', 10, 2)->nullable();
            $table->timestamps(); // created_at, updated_at
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
