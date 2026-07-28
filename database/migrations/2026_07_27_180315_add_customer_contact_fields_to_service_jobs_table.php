<?php

use App\Enums\JobWorkflowStatus;
use App\Models\Client;
use App\Models\Job;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
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

    /**
     * Reverse the migrations.
     */
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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
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
                    j.longitude = COALESCE(j.longitude, c.longitude),
                    j.required_services = COALESCE(j.required_services, c.service_types),
                    j.site_instructions = COALESCE(j.site_instructions, c.additional_site_instructions),
                    j.estimated_duration_minutes = COALESCE(j.estimated_duration_minutes, c.estimated_time),
                    j.charges = COALESCE(j.charges, c.charges),
                    j.payment_mode = COALESCE(j.payment_mode, c.payment_mode),
                    j.payment_status = COALESCE(j.payment_status, c.payment_status),
                    j.customer_type = COALESCE(j.customer_type, c.customer_type),
                    j.parking_status = COALESCE(j.parking_status, c.parking_status),
                    j.pet_warning = COALESCE(j.pet_warning, c.pet_warning),
                    j.special_remarks = COALESCE(j.special_remarks, c.special_remarks),
                    j.zone_id = COALESCE(j.zone_id, c.zone_id),
                    j.equipment_type_id = COALESCE(j.equipment_type_id, c.equipment_type_id),
                    j.job_level_id = COALESCE(j.job_level_id, c.job_level_id),
                    j.recurrence_id = COALESCE(j.recurrence_id, c.recurrence_id),
                    j.lead_id = COALESCE(j.lead_id, c.lead_id)
            ');

            return;
        }

        Job::query()
            ->whereNotNull('client_id')
            ->with('client')
            ->each(function (Job $job): void {
                $client = $job->client;
                if ($client === null) {
                    return;
                }

                $job->forceFill($this->customerSnapshotFromClient($client, $job))->saveQuietly();
            });
    }

    private function createJobsForClientsWithoutJobs(): void
    {
        Client::query()
            ->whereDoesntHave('jobs')
            ->orderBy('id')
            ->each(function (Client $client): void {
                Job::query()->create(array_merge(
                    $this->customerSnapshotFromClient($client),
                    [
                        'client_id' => $client->id,
                        'lead_id' => $client->lead_id,
                        'zone_id' => $client->zone_id,
                        'equipment_type_id' => $client->equipment_type_id,
                        'job_level_id' => $client->job_level_id,
                        'recurrence_id' => $client->recurrence_id,
                        'client_address' => $client->address,
                        'latitude' => $client->latitude,
                        'longitude' => $client->longitude,
                        'required_services' => $client->service_types ?? [],
                        'scheduled_date' => $client->schedule_date ?? now()->toDateString(),
                        'estimated_duration_minutes' => $client->estimated_time ?: 60,
                        'site_instructions' => $client->additional_site_instructions,
                        'parking_status' => $client->parking_status,
                        'customer_type' => $client->customer_type,
                        'pet_warning' => $client->pet_warning,
                        'payment_mode' => $client->payment_mode,
                        'payment_status' => $client->payment_status,
                        'charges' => $client->charges,
                        'special_remarks' => $client->special_remarks,
                        'status' => JobWorkflowStatus::HOLD->value,
                        'created_by' => $client->created_by,
                    ]
                ));
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function customerSnapshotFromClient(Client $client, ?Job $job = null): array
    {
        return [
            'customer_name' => $job?->customer_name ?: $client->name,
            'email' => $job?->email ?: $client->email,
            'phone' => $job?->phone ?: $client->phone,
            'weed_spray' => $job?->weed_spray ?: $client->weed_spray,
            'job_type' => $job?->job_type ?: $client->job_type,
            'property_details' => $job?->property_details ?: $client->property_details,
            'notes' => $job?->notes ?: $client->notes,
            'accounting_level_id' => $job?->accounting_level_id ?: $client->accounting_level_id,
            'client_rating_id' => $job?->client_rating_id ?: $client->client_rating_id,
        ];
    }
};
