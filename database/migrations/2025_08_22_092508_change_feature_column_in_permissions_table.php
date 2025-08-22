<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'menu')) {
                $table->string('menu')->nullable()->after('name');
            }
            if (!Schema::hasColumn('permissions', 'feature')) {
                $table->string('feature')->nullable()->after('menu');
            }
            if (!Schema::hasColumn('permissions', 'route')) {
                $table->string('route')->nullable()->after('feature');
            }
            if (!Schema::hasColumn('permissions', 'alias')) {
                $table->string('alias')->nullable()->after('route');
            }
            if (!Schema::hasColumn('permissions', 'status')) {
                $table->boolean('status')->default(true)->after('alias');
            }
            if (!Schema::hasColumn('permissions', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->json('feature')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('feature')->nullable()->change();
            $table->dropColumn(['menu', 'feature', 'route', 'alias', 'status']);
            $table->dropSoftDeletes();
        });
    }
};
