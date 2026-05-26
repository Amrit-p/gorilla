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
            $table->json('service_types')->nullable()->after('address');
            $table->json('safety_concerns')->nullable()->after('job_type');
        });

        DB::table('clients')->orderBy('id')->chunkById(100, function ($clients): void {
            foreach ($clients as $client) {
                $updates = [];
                if (! empty($client->service_type)) {
                    $updates['service_types'] = json_encode([$client->service_type]);
                }
                if (! empty($client->safety)) {
                    $updates['safety_concerns'] = json_encode([$client->safety]);
                }
                if ($updates !== []) {
                    DB::table('clients')->where('id', $client->id)->update($updates);
                }
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'safety']);
        });

        $renames = config('roles.legacy_names', []);
        foreach ($renames as $from => $to) {
            DB::table('roles')->where('name', $from)->update(['name' => $to]);
        }

        DB::table('roles')->updateOrInsert(
            ['name' => 'Sales Person', 'guard_name' => 'web'],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $salesPersonRoleId = DB::table('roles')->where('name', 'Sales Person')->value('id');
        $salesManagerPermissionIds = DB::table('role_has_permissions')
            ->where('role_id', DB::table('roles')->where('name', 'Sales Manager')->value('id'))
            ->pluck('permission_id');

        foreach ($salesManagerPermissionIds as $permissionId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'role_id' => $salesPersonRoleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('service_type', 40)->nullable()->after('address');
            $table->string('safety', 30)->nullable()->after('job_type');
        });

        DB::table('clients')->orderBy('id')->chunkById(100, function ($clients): void {
            foreach ($clients as $client) {
                $serviceTypes = json_decode((string) ($client->service_types ?? '[]'), true);
                $safetyConcerns = json_decode((string) ($client->safety_concerns ?? '[]'), true);
                DB::table('clients')->where('id', $client->id)->update([
                    'service_type' => is_array($serviceTypes) ? ($serviceTypes[0] ?? null) : null,
                    'safety' => is_array($safetyConcerns) ? ($safetyConcerns[0] ?? null) : null,
                ]);
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['service_types', 'safety_concerns']);
        });

        DB::table('roles')->where('name', 'Sales Person')->delete();

        $renames = array_flip(config('roles.legacy_names', []));
        foreach ($renames as $from => $to) {
            DB::table('roles')->where('name', $from)->update(['name' => $to]);
        }
    }
};
