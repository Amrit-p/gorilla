<?php

use App\Support\CrmPermissions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        CrmPermissions::migrateLegacyAssignments();
    }

    public function down(): void
    {
        // Permissions are canonical; reversing would break role assignments.
    }
};
