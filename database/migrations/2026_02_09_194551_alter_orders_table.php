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
        Schema::table('orders', function(Blueprint $blueprint) {
            $blueprint->string('service_request_number', 10)->nullable();
            $blueprint->date('service_request_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function(Blueprint $blueprint) {
            $blueprint->dropColumn('service_request_number');
            $blueprint->dropColumn('service_request_date');
        });
    }
};
