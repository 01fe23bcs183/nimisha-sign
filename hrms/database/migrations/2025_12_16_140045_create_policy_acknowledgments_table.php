<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_policy_id');
            $table->unsignedBigInteger('staff_member_id');
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->foreign('company_policy_id')->references('id')->on('company_policies')->onDelete('cascade');
            $table->foreign('staff_member_id')->references('id')->on('staff_members')->onDelete('cascade');
            $table->unique(['company_policy_id', 'staff_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_acknowledgments');
    }
};
