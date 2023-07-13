<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    private function getTableName(): string
    {
        $tableName = config('patch-runner.patches_table');

        if (!$tableName) {
            throw new Exception('Incorrect table name for patches list. Please check your patch-runner.php config file');
        }

        return $tableName;
    }

    public function up(): void
    {
        Schema::create($this->getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index();
            $table->timestamp('applied_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->getTableName());
    }
};
