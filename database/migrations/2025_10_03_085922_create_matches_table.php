<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();

            // owner of this profile (who created it)
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();

            // basic identity
            $table->string('family_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('date_of_birth')->nullable();

            // photos stored as path strings (storage/app/public/...)
            $table->string('profile_photo')->nullable();
            $table->string('photo1')->nullable();
            $table->string('photo2')->nullable();
            $table->string('photo3')->nullable();

            // parents
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_occupation')->nullable();

            // education & work
            $table->text('educational_qualifications')->nullable();
            $table->string('profession')->nullable();        // e.g. Civil Engineer, Medicine, Law
            $table->string('designation')->nullable();
            $table->string('company_name')->nullable();
            $table->string('place_of_working')->nullable();

            // descriptive
            $table->text('about_self')->nullable();
            $table->text('about_family')->nullable();

            // Denormalized latest payment info for quick reads (nullable)
            // amount is integer rupees; switch to paise integer if you prefer.
            $table->integer('amount')->nullable()->comment('Amount paid for this match (rupees)');
            $table->string('payment_id')->nullable()->comment('Payment identifier (Razorpay payment_id or internal)');
            $table->timestamp('payment_date')->nullable();

            // meta
            $table->boolean('is_published')->default(false)->index(); // optional: publish status
            $table->timestamps();

            // index to search by name
            $table->index(['family_name', 'first_name', 'last_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
