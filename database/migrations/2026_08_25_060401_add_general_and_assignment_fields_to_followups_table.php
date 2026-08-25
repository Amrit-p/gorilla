<?php

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
        Schema::table('followups', function (Blueprint $table): void {
            $table->string('title')->nullable()->after('followable_id');
            $table->foreignId('assigned_to')->nullable()->after('created_by')
                ->constrained('users')->nullOnDelete();
        });

        // Notes becomes the primary (required) field, outcome becomes optional.
        // Move legacy outcome text into notes wherever notes was never filled in.
        DB::table('followups')
            ->where(function ($query): void {
                $query->whereNull('notes')->orWhere('notes', '');
            })
            ->update([
                'notes' => DB::raw('outcome'),
                'outcome' => null,
            ]);

        Schema::table('followups', function (Blueprint $table): void {
            $table->string('followable_type')->nullable()->change();
            $table->unsignedBigInteger('followable_id')->nullable()->change();
            $table->text('outcome')->nullable()->change();
            $table->text('notes')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // General follow-ups have no polymorphic target and cannot exist in the old schema.
        DB::table('followups')->whereNull('followable_type')->delete();

        DB::table('followups')
            ->where(function ($query): void {
                $query->whereNull('outcome')->orWhere('outcome', '');
            })
            ->update(['outcome' => DB::raw('notes')]);

        Schema::table('followups', function (Blueprint $table): void {
            $table->text('outcome')->nullable(false)->change();
            $table->text('notes')->nullable()->change();
            $table->string('followable_type')->nullable(false)->change();
            $table->unsignedBigInteger('followable_id')->nullable(false)->change();
        });

        Schema::table('followups', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn('title');
        });
    }
};
