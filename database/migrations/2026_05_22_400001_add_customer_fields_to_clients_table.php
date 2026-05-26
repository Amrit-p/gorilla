<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->unsignedInteger('customer_unique_id')->nullable()->unique()->after('id');
            $table->string('parking_status', 40)->nullable()->after('client_type');
            $table->string('customer_type', 40)->nullable()->after('parking_status');
            $table->text('additional_site_instructions')->nullable()->after('property_details');
            $table->text('pet_warning')->nullable()->after('additional_site_instructions');
            $table->text('special_remarks')->nullable()->after('pet_warning');
        });

        $nextId = (int) config('mowing.customer_unique_id_start', 2001);
        foreach (\App\Models\Client::withTrashed()->orderBy('id')->get() as $client) {
            if ($client->customer_unique_id !== null) {
                continue;
            }
            \Illuminate\Support\Facades\DB::table('clients')
                ->where('id', $client->id)
                ->update(['customer_unique_id' => $nextId++]);
        }
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn([
                'customer_unique_id',
                'parking_status',
                'customer_type',
                'additional_site_instructions',
                'pet_warning',
                'special_remarks',
            ]);
        });
    }
};
