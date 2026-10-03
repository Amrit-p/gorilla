<?php

use App\Enums\JobOperationalPaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Partial payments recorded the amount paid in first_payment while received
     * payments used received_amount. One amount column now covers both, so the
     * recorded partials move across before the old column goes.
     */
    public function up(): void
    {
        DB::table('service_jobs')
            ->whereNotNull('first_payment')
            ->whereNull('received_amount')
            ->update(['received_amount' => DB::raw('first_payment')]);

        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->dropColumn('first_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->decimal('first_payment', 10, 2)->nullable()->after('charges');
        });

        DB::table('service_jobs')
            ->where('payment_status', JobOperationalPaymentStatus::PARTIAL->value)
            ->whereNotNull('received_amount')
            ->update(['first_payment' => DB::raw('received_amount')]);
    }
};
