<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_member_id');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'half_day', 'on_leave', 'holiday'])->default('present');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leaving_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->integer('total_rest_minutes')->default(0);
            $table->integer('total_work_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->timestamps();

            $table->foreign('staff_member_id')->references('id')->on('staff_members')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
            $table->unique(['staff_member_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
