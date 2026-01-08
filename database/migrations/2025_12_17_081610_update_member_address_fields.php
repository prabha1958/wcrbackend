
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {

            // Add new address fields
            $table->string('address_flat_number')->nullable()->after('membership_fee');
            $table->string('address_premises')->nullable()->after('address_flat_number');
            $table->string('address_area')->nullable()->after('address_premises');
            $table->string('address_landmark')->nullable()->after('address_area');
            $table->string('address_city')->nullable()->after('address_landmark');
            $table->string('address_pin', 6)->nullable()->after('address_city');

            // Remove JSON & old TEXT fields if they exist
            if (Schema::hasColumn('members', 'residential_address')) {
                $table->dropColumn('residential_address');
            }
            if (Schema::hasColumn('members', 'residential_address_json')) {
                $table->dropColumn('residential_address_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {

            // Restore old JSON field (optional)
            $table->json('residential_address_json')->nullable();

            // Restore old text field (optional)
            $table->text('residential_address')->nullable();

            // Drop new address fields
            $table->dropColumn([
                'address_flat_number',
                'address_premises',
                'address_area',
                'address_landmark',
                'address_city',
                'address_pin'
            ]);
        });
    }
};
