<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scopes', function (Blueprint $table) {
            $table->renameColumn('comments', 'checklist_comments');
        });
    }

    public function down(): void
    {
        Schema::table('scopes', function (Blueprint $table) {
            $table->renameColumn('checklist_comments', 'comments');
        });
    }
};
