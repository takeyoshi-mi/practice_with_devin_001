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
        Schema::create('ticket_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('added_by_user_id')->constrained('users');
            $table->integer('ticket_count')->comment('付与チケット数');
            $table->enum('action_type', ['add', 'subtract', 'expire'])->default('add');
            $table->string('notes')->nullable()->comment('備考');
            $table->timestamps();
            
            $table->index(['student_id', 'created_at']);
            $table->index('added_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_histories');
    }
};
