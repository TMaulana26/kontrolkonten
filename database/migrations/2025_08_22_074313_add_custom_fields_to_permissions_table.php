<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('menu')->nullable()->after('name');
            $table->string('feature')->nullable()->after('menu');
            $table->string('route')->nullable()->after('feature');
            $table->string('alias')->nullable()->after('route');
            $table->boolean('status')->default(true)->after('alias');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['menu', 'feature', 'route', 'alias', 'status']);
            $table->dropSoftDeletes();
        });
    }
};
