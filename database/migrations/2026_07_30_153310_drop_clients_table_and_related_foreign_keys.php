<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remove the Customer (clients) entity entirely.
 * Contact data already lives on service_jobs; client_ratings master is kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->ensureMowerRemarksJobId();
        $this->rebuildMowerRemarksWithoutClientId();
        $this->dropServiceJobsClientId();
        $this->dropClientDocuments();
        $this->dropClientsTable();
    }

    public function down(): void
    {
        // Irreversible data removal — recreate empty shell tables only.
        if (! Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasColumn('service_jobs', 'client_id')) {
            Schema::table('service_jobs', function (Blueprint $table): void {
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('mower_remarks', 'client_id')) {
            Schema::table('mower_remarks', function (Blueprint $table): void {
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            });
        }
    }

    private function ensureMowerRemarksJobId(): void
    {
        if (! Schema::hasTable('mower_remarks')) {
            return;
        }

        if (! Schema::hasColumn('mower_remarks', 'job_id')) {
            Schema::table('mower_remarks', function (Blueprint $table): void {
                $table->foreignId('job_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('service_jobs')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('mower_remarks', 'client_id')) {
            return;
        }

        $remarks = DB::table('mower_remarks')->whereNull('job_id')->whereNotNull('client_id')->get(['id', 'client_id']);
        foreach ($remarks as $remark) {
            $jobId = DB::table('service_jobs')
                ->where('client_id', $remark->client_id)
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->value('id');

            if ($jobId) {
                DB::table('mower_remarks')->where('id', $remark->id)->update(['job_id' => $jobId]);
            }
        }
    }

    private function rebuildMowerRemarksWithoutClientId(): void
    {
        if (! Schema::hasTable('mower_remarks') || ! Schema::hasColumn('mower_remarks', 'client_id')) {
            return;
        }

        Schema::create('mower_remarks_tmp', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->nullable()->constrained('service_jobs')->nullOnDelete();
            $table->text('description');
            $table->timestamps();
        });

        $rows = DB::table('mower_remarks')->get();
        foreach ($rows as $row) {
            DB::table('mower_remarks_tmp')->insert([
                'id' => $row->id,
                'user_id' => $row->user_id,
                'job_id' => $row->job_id ?? null,
                'description' => $row->description,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop('mower_remarks');
        Schema::rename('mower_remarks_tmp', 'mower_remarks');
    }

    private function dropServiceJobsClientId(): void
    {
        if (! Schema::hasColumn('service_jobs', 'client_id')) {
            return;
        }

        // Drop foreign key if present (name varies by driver / history).
        try {
            Schema::table('service_jobs', function (Blueprint $table): void {
                $table->dropForeign(['client_id']);
            });
        } catch (Throwable) {
            // SQLite / already dropped
        }

        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->dropColumn('client_id');
        });
    }

    private function dropClientDocuments(): void
    {
        Schema::dropIfExists('client_documents');
    }

    private function dropClientsTable(): void
    {
        // Drop FKs that may still point at clients from historical migrations.
        if (Schema::hasTable('clients')) {
            Schema::drop('clients');
        }
    }
};
