
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alliances', function (Blueprint $table) {
            if (Schema::hasColumn('alliances', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('alliances', 'payment_id')) {
                $table->dropColumn('payment_id');
            }
            if (Schema::hasColumn('alliances', 'payment_date')) {
                $table->dropColumn('payment_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alliances', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('payment_id')->nullable();
            $table->date('payment_date')->nullable();
        });
    }
};
