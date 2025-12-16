<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saturation_deductions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_member_id');
            $table->unsignedBigInteger('deduction_option_id');
            $table->string('title');
            $table->enum('type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('amount', 15, 2)->default(0);
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->timestamps();

            $table->foreign('staff_member_id')->references('id')->on('staff_members')->onDelete('cascade');
            $table->foreign('deduction_option_id')->references('id')->on('deduction_options')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saturation_deductions');
    }
};
