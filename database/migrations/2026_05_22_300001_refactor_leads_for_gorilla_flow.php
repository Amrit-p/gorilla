<?php

use App\Enums\LeadStatus;
use App\Models\EquipmentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->string('client_name')->nullable()->after('id');
            $table->string('mobile_number', 30)->nullable()->after('email');
            $table->text('remarks')->nullable()->after('property_details');
            $table->foreignId('equipment_type_id')->nullable()->after('weed_spray')->constrained('equipment_types')->nullOnDelete();
            $table->string('payment_status', 30)->nullable()->after('payment_mode');
            $table->date('lead_date')->nullable()->after('longitude');
            $table->time('lead_time')->nullable()->after('lead_date');
        });

        foreach (DB::table('leads')->orderBy('id')->get() as $lead) {
            $equipmentTypeId = null;
            if (! empty($lead->equipment_type)) {
                $equipmentTypeId = EquipmentType::query()
                    ->where('name', $lead->equipment_type)
                    ->value('id');
            }

            DB::table('leads')->where('id', $lead->id)->update([
                'client_name' => $lead->name,
                'mobile_number' => $lead->phone,
                'remarks' => $lead->notes,
                'equipment_type_id' => $equipmentTypeId,
                'payment_status' => 'Pending',
            ]);
        }

        Schema::table('leads', function (Blueprint $table): void {
            if (Schema::hasColumn('leads', 'phone')) {
                $table->dropIndex(['phone']);
            }
            $table->dropColumn(['name', 'phone', 'notes', 'equipment_type']);
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->index('mobile_number');
            $table->index('client_name');
        });

        $statusMap = [
            'Contacted' => LeadStatus::FOLLOW_UP->value,
            'Quote Sent' => LeadStatus::FOLLOW_UP->value,
            'Follow-up' => LeadStatus::FOLLOW_UP->value,
            'Mature/Won' => LeadStatus::WON->value,
        ];

        foreach ($statusMap as $from => $to) {
            DB::table('leads')->where('status', $from)->update(['status' => $to]);
        }

        DB::table('leads')
            ->where('status', LeadStatus::WON->value)
            ->whereNull('converted_at')
            ->update(['status' => LeadStatus::MATURE->value]);

        Schema::table('clients', function (Blueprint $table): void {
            $table->unique('lead_id');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropUnique(['lead_id']);
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->string('name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('notes')->nullable();
            $table->string('equipment_type', 40)->nullable();
        });

        foreach (DB::table('leads')->orderBy('id')->get() as $lead) {
            $equipmentName = $lead->equipment_type_id
                ? EquipmentType::query()->whereKey($lead->equipment_type_id)->value('name')
                : null;

            DB::table('leads')->where('id', $lead->id)->update([
                'name' => $lead->client_name,
                'phone' => $lead->mobile_number,
                'notes' => $lead->remarks,
                'equipment_type' => $equipmentName,
            ]);
        }

        Schema::table('leads', function (Blueprint $table): void {
            if (Schema::hasColumn('leads', 'mobile_number')) {
                $table->dropIndex(['mobile_number']);
            }
            if (Schema::hasColumn('leads', 'client_name')) {
                $table->dropIndex(['client_name']);
            }
            $table->dropConstrainedForeignId('equipment_type_id');
            $table->dropColumn(['client_name', 'mobile_number', 'remarks', 'payment_status', 'lead_date', 'lead_time']);
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->index('phone');
        });
    }
};
