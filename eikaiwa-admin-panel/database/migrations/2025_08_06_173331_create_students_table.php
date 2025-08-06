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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 20)->unique()->comment('生徒ID');
            $table->string('nickname', 100)->comment('ニックネーム');
            $table->string('name', 100)->comment('氏名');
            $table->string('email')->unique()->comment('メールアドレス');
            $table->integer('remaining_tickets')->default(0)->comment('残チケット数');
            $table->timestamps();
            
            $table->index('student_id');
            $table->index('email');
            $table->index(['name', 'nickname']); // 検索用複合インデックス
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
