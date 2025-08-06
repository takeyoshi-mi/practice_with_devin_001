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
        Schema::create('student_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('plan_name', 100)->comment('プラン名');
            $table->date('start_date')->comment('開始日');
            $table->date('finish_date')->nullable()->comment('終了日');
            $table->boolean('is_active')->default(true)->comment('アクティブフラグ');
            $table->timestamps();
            
            $table->index(['student_id', 'is_active']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_plans');
    }
};
