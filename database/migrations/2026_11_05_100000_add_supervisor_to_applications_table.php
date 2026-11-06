<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('preferred_supervisor')->nullable()->after('programme_mode');
            $table->string('supervisor_department')->nullable()->after('preferred_supervisor');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['preferred_supervisor', 'supervisor_department']);
        });
    }
};
