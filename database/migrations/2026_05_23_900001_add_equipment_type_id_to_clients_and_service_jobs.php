<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('equipment_type_id')
                ->nullable()
                ->after('lead_id')
                ->constrained('equipment_types')
                ->nullOnDelete();
        });

        Schema::table('service_jobs', function (Blueprint $table) {
            $table->foreignId('equipment_type_id')
                ->nullable()
                ->after('lead_id')
                ->constrained('equipment_types')
                ->nullOnDelete();
        });

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::table('clients')
                ->whereNotNull('lead_id')
                ->update([
                    'equipment_type_id' => DB::raw('(SELECT equipment_type_id FROM leads WHERE leads.id = clients.lead_id)'),
                ]);

            DB::table('service_jobs')
                ->whereNotNull('client_id')
                ->update([
                    'equipment_type_id' => DB::raw('(SELECT equipment_type_id FROM clients WHERE clients.id = service_jobs.client_id)'),
                ]);

            DB::table('service_jobs')
                ->whereNull('equipment_type_id')
                ->whereNotNull('lead_id')
                ->update([
                    'equipment_type_id' => DB::raw('(SELECT equipment_type_id FROM leads WHERE leads.id = service_jobs.lead_id)'),
                ]);
        } else {
            DB::statement('
                UPDATE clients AS c
                INNER JOIN leads AS l ON l.id = c.lead_id
                SET c.equipment_type_id = l.equipment_type_id
                WHERE l.equipment_type_id IS NOT NULL
            ');

            DB::statement('
                UPDATE service_jobs AS j
                INNER JOIN clients AS c ON c.id = j.client_id
                SET j.equipment_type_id = c.equipment_type_id
                WHERE c.equipment_type_id IS NOT NULL
            ');

            DB::statement('
                UPDATE service_jobs AS j
                INNER JOIN leads AS l ON l.id = j.lead_id
                SET j.equipment_type_id = l.equipment_type_id
                WHERE j.equipment_type_id IS NULL
                  AND l.equipment_type_id IS NOT NULL
            ');
        }
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('equipment_type_id');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('equipment_type_id');
        });
    }
};
