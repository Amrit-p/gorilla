<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->json('service_types')->nullable()->after('address');
        });

        DB::table('leads')->orderBy('id')->chunkById(100, function ($leads): void {
            foreach ($leads as $lead) {
                if (empty($lead->service_type)) {
                    continue;
                }

                DB::table('leads')->where('id', $lead->id)->update([
                    'service_types' => json_encode([$lead->service_type]),
                ]);
            }
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn('service_type');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->string('service_type', 40)->nullable()->after('address');
        });

        DB::table('leads')->orderBy('id')->chunkById(100, function ($leads): void {
            foreach ($leads as $lead) {
                $types = json_decode((string) ($lead->service_types ?? '[]'), true);
                if (! is_array($types) || $types === []) {
                    continue;
                }

                DB::table('leads')->where('id', $lead->id)->update([
                    'service_type' => $types[0] ?? null,
                ]);
            }
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn('service_types');
        });
    }
};
