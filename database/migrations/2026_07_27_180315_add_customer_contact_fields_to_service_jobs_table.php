<?php

use App\Enums\JobWorkflowStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('client_id');
            $table->string('email')->nullable()->after('customer_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('weed_spray')->nullable()->after('phone');
            $table->string('job_type')->nullable()->after('weed_spray');
            $table->text('property_details')->nullable()->after('job_type');
            $table->text('notes')->nullable()->after('property_details');
            $table->foreignId('accounting_level_id')->nullable()->after('notes')->constrained('accounting_levels')->nullOnDelete();
            $table->foreignId('client_rating_id')->nullable()->after('accounting_level_id')->constrained('client_ratings')->nullOnDelete();
        });

        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('service_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->change();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
        });

        $this->backfillJobsFromClients();
        $this->createJobsForClientsWithoutJobs();
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropForeign(['accounting_level_id']);
            $table->dropForeign(['client_rating_id']);
            $table->dropColumn([
                'customer_name',
                'email',
                'phone',
                'weed_spray',
                'job_type',
                'property_details',
                'notes',
                'accounting_level_id',
                'client_rating_id',
            ]);
        });
    }

    private function backfillJobsFromClients(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('
                UPDATE service_jobs AS j
                INNER JOIN clients AS c ON c.id = j.client_id
                SET
                    j.customer_name = COALESCE(j.customer_name, c.name),
                    j.email = COALESCE(j.email, c.email),
                    j.phone = COALESCE(j.phone, c.phone),
                    j.weed_spray = COALESCE(j.weed_spray, c.weed_spray),
                    j.job_type = COALESCE(j.job_type, c.job_type),
                    j.property_details = COALESCE(j.property_details, c.property_details),
                    j.notes = COALESCE(j.notes, c.notes),
                    j.accounting_level_id = COALESCE(j.accounting_level_id, c.accounting_level_id),
                    j.client_rating_id = COALESCE(j.client_rating_id, c.client_rating_id),
                    j.client_address = COALESCE(j.client_address, c.address),
                    j.latitude = COALESCE(j.latitude, c.latitude),
                    j.longitude = COALESCE(j.longitude, c.longitude)
            ');

            return;
        }

        foreach (DB::table('service_jobs')->whereNotNull('client_id')->get() as $job) {
            $client = DB::table('clients')->where('id', $job->client_id)->first();
            if ($client === null) {
                continue;
            }

            DB::table('service_jobs')->where('id', $job->id)->update([
                'customer_name' => $job->customer_name ?: $client->name,
                'email' => $job->email ?: $client->email,
                'phone' => $job->phone ?: $client->phone,
                'weed_spray' => $job->weed_spray ?: $client->weed_spray,
                'job_type' => $job->job_type ?: $client->job_type,
                'property_details' => $job->property_details ?: $client->property_details,
                'notes' => $job->notes ?: $client->notes,
                'accounting_level_id' => $job->accounting_level_id ?: $client->accounting_level_id,
                'client_rating_id' => $job->client_rating_id ?: $client->client_rating_id,
                'client_address' => $job->client_address ?: $client->address,
                'latitude' => $job->latitude ?: $client->latitude,
                'longitude' => $job->longitude ?: $client->longitude,
            ]);
        }
    }

    private function createJobsForClientsWithoutJobs(): void
    {
        $clientIdsWithJobs = DB::table('service_jobs')
            ->whereNotNull('client_id')
            ->distinct()
            ->pluck('client_id')
            ->all();

        $clients = DB::table('clients')
            ->when($clientIdsWithJobs !== [], fn ($q) => $q->whereNotIn('id', $clientIdsWithJobs))
            ->orderBy('id')
            ->get();

        $now = now();

        foreach ($clients as $client) {
            $serviceTypes = $client->service_types;
            if (is_string($serviceTypes)) {
                $decoded = json_decode($serviceTypes, true);
                $serviceTypes = is_array($decoded) ? $serviceTypes : json_encode([]);
            } elseif (is_array($serviceTypes)) {
                $serviceTypes = json_encode($serviceTypes);
            } else {
                $serviceTypes = json_encode([]);
            }

            DB::table('service_jobs')->insert([
                'client_id' => $client->id,
                'lead_id' => $client->lead_id,
                'zone_id' => $client->zone_id,
                'equipment_type_id' => $client->equipment_type_id,
                'job_level_id' => $client->job_level_id ?? null,
                'recurrence_id' => $client->recurrence_id,
                'customer_name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'weed_spray' => $client->weed_spray,
                'job_type' => $client->job_type,
                'property_details' => $client->property_details,
                'notes' => $client->notes,
                'accounting_level_id' => $client->accounting_level_id ?? null,
                'client_rating_id' => $client->client_rating_id ?? null,
                'client_address' => $client->address,
                'latitude' => $client->latitude,
                'longitude' => $client->longitude,
                'required_services' => $serviceTypes,
                'scheduled_date' => $client->schedule_date ?? $now->toDateString(),
                'estimated_duration_minutes' => is_numeric($client->estimated_time ?? null) ? (int) $client->estimated_time : 60,
                'site_instructions' => $client->additional_site_instructions ?? null,
                'parking_status' => $client->parking_status,
                'customer_type' => $client->customer_type,
                'pet_warning' => $client->pet_warning,
                'payment_mode' => $client->payment_mode,
                'payment_status' => $client->payment_status,
                'charges' => $client->charges,
                'special_remarks' => $client->special_remarks,
                'status' => JobWorkflowStatus::HOLD->value,
                'created_by' => $client->created_by,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
