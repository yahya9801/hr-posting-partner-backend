<?php

// database/migrations/xxxx_xx_xx_add_experience_to_jobs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->enum('experience', ['Yes', 'No', 'Less than 1 year'])->default('Yes')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('experience');
        });
    }
};
