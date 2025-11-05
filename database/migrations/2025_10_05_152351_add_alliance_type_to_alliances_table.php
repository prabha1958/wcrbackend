<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('alliances', function (Blueprint $table) {
            $table->enum('alliance_type', ['bride', 'bridegroom'])
                ->nullable()
                ->after('member_id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('alliances', function (Blueprint $table) {
            $table->dropColumn('alliance_type');
        });
    }
};
