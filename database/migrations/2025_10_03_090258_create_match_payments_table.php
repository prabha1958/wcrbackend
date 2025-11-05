<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            // payer (member who paid)
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();

            // payment gateway / order references
            $table->string('payment_gateway')->nullable()->comment('eg: razorpay');
            $table->string('payment_gateway_order_id')->nullable();
            $table->string('payment_gateway_payment_id')->nullable();
            $table->string('payment_gateway_signature')->nullable();

            // amount stored as integer rupees (or paise if you prefer)
            $table->integer('amount')->default(0);
            $table->string('currency', 10)->default('INR');

            $table->string('status')->default('created'); // created, paid, failed, refunded
            $table->timestamp('paid_at')->nullable();

            // optional metadata / raw response for debugging
            $table->json('raw')->nullable();

            $table->timestamps();

            $table->index(['match_id']);
            $table->index(['member_id']);
            $table->index(['payment_gateway_payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_payments');
    }
};
