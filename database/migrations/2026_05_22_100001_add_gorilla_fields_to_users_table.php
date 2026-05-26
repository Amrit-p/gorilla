<?php

use App\Enums\UserEfficiency;
use App\Enums\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('user_unique_id')->nullable()->unique()->after('id');
            $table->string('efficiency', 20)->default(UserEfficiency::AVERAGE->value)->after('phone');
            $table->string('status', 20)->default(UserStatus::ACTIVE->value)->after('efficiency')->index();
        });

        $startId = (int) config('mowing.user_unique_id_start', 1001);
        $counter = $startId;

        DB::table('users')->orderBy('id')->chunkById(100, function ($users) use (&$counter): void {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'user_unique_id' => $counter,
                    'efficiency' => UserEfficiency::AVERAGE->value,
                    'status' => ($user->is_active ?? true) ? UserStatus::ACTIVE->value : UserStatus::INACTIVE->value,
                ]);
                $counter++;
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_unique_id', 'efficiency', 'status']);
        });
    }
};
