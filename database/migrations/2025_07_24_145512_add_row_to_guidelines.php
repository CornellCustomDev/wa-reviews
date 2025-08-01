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
        DB::table('criteria')->insert([
            'name' => 'Non-WCAG User Experience',
            'number' => 'UX',
            'level' => 'UX',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $id = DB::table('criteria')->where('number', 'UX')->value('id');

        // If category_id 11 does not exist, create it
        if (!DB::table('categories')->where('id', 11)->exists()) {
            DB::table('categories')->insert([
                'id' => 11,
                'name' => 'Best Practice',
                'description' => 'Best practices which assure quality for accessible user experience.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('guidelines')->insert([
            'id' => 102,
            'number' => 102,
            'name' => 'Non-WCAG user experience issue.',
            'notes' => '<h3>WCAG 2 criterion</h3><p>Non-WCAG</p><h3>Tools and requirements</h3><h3>Note</h3><p>See observed functionality.</p>',
            'tools' => '[]',
            'criterion_id' => $id,
            'category_id' => 11,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('guidelines')->where('number', '102')->delete();
        DB::table('criteria')->where('number', 'UX')->delete();
    }

};
