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
        Schema::create('women_fellowships', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->date('date_of_event'); // date of the event
            $table->json('members_present'); // JSON array of member ids or names
            $table->string('sermon_by'); // preacher/speaker
            $table->json('event_photos')->nullable(); // JSON array of up to 4 filenames
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('women_fellowships');
    }
};
