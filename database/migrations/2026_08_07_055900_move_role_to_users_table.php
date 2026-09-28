<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add role_id column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
        });

        // 2. Migrate data from role_user pivot table to users.role_id
        if (Schema::hasTable('role_user')) {
            $pivotRows = DB::table('role_user')->get();
            foreach ($pivotRows as $row) {
                DB::table('users')
                    ->where('id', $row->user_id)
                    ->update(['role_id' => $row->role_id]);
            }
        }

        // 3. Drop the role_user pivot table
        Schema::dropIfExists('role_user');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Recreate role_user pivot table
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // 2. Migrate data back from users.role_id to role_user
        $users = DB::table('users')->whereNotNull('role_id')->get();
        foreach ($users as $user) {
            DB::table('role_user')->insert([
                'role_id' => $user->role_id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Drop role_id from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
