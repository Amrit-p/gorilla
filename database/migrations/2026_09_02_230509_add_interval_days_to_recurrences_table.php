<?php

use App\Models\Recurrence;
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
        Schema::table('recurrences', function (Blueprint $table): void {
            $table->unsignedSmallInteger('interval_days')->nullable()->after('name');
        });

        // Seed a best-effort day count for existing rows (e.g. "3-Weekly" → 21).
        // Anything it can't infer is left null — editable by hand afterwards.
        DB::table('recurrences')->select(['id', 'name'])->orderBy('id')->each(function (object $row): void {
            $days = Recurrence::parseIntervalDays((string) $row->name);

            if ($days !== null) {
                DB::table('recurrences')->where('id', $row->id)->update(['interval_days' => $days]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recurrences', function (Blueprint $table): void {
            $table->dropColumn('interval_days');
        });
    }
};
