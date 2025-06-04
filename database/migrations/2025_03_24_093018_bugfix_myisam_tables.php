<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix MyISAM tables to InnoDB.
     */
    public function up(): void
    {
        // Don't run this on a SQLite database
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE permissions ENGINE=InnoDB');
        DB::statement('ALTER TABLE permission_role ENGINE=InnoDB');
        DB::statement('ALTER TABLE permission_user ENGINE=InnoDB');
        DB::statement('ALTER TABLE roles ENGINE=InnoDB');
        DB::statement('ALTER TABLE role_user ENGINE=InnoDB');
        DB::statement('ALTER TABLE teams ENGINE=InnoDB');
        DB::statement('ALTER TABLE team_user ENGINE=InnoDB');
    }

    public function down(): void
    {
        //
    }
};
