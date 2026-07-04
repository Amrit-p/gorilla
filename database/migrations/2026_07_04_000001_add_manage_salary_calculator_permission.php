<?php

use App\Support\CrmPermissions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        CrmPermissions::syncRolesAndPermissions();
    }

    public function down(): void
    {
        //
    }
};
