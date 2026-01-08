<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('couple_pic')->nullable()->after('profile_photo');
            // new JSON column - keep existing text column intact
            $table->json('residential_address_json')->nullable()->after('residential_address');
        });

        // copy existing free-text into the new JSON column under key 'raw'
        DB::table('members')->select('id', 'residential_address')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $json = $row->residential_address !== null
                        ? json_encode(['raw' => $row->residential_address])
                        : null;
                    DB::table('members')->where('id', $row->id)
                        ->update(['residential_address_json' => $json]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('couple_pic');
            $table->dropColumn('residential_address_json');
        });
    }
};
