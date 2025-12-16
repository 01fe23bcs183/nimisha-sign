<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_member_id');
            $table->unsignedBigInteger('previous_office_location_id')->nullable();
            $table->unsignedBigInteger('new_office_location_id');
            $table->unsignedBigInteger('previous_division_id')->nullable();
            $table->unsignedBigInteger('new_division_id')->nullable();
            $table->date('effective_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->timestamps();

            $table->foreign('staff_member_id')->references('id')->on('staff_members')->onDelete('cascade');
            $table->foreign('previous_office_location_id')->references('id')->on('office_locations')->onDelete('set null');
            $table->foreign('new_office_location_id')->references('id')->on('office_locations')->onDelete('cascade');
            $table->foreign('previous_division_id')->references('id')->on('divisions')->onDelete('set null');
            $table->foreign('new_division_id')->references('id')->on('divisions')->onDelete('set null');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_transfers');
    }
};
