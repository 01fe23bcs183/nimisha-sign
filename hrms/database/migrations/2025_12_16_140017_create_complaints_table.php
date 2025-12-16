<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complaint_from');
            $table->unsignedBigInteger('complaint_against')->nullable();
            $table->unsignedBigInteger('complaint_against_division')->nullable();
            $table->string('title');
            $table->date('complaint_date');
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open');
            $table->text('resolution')->nullable();
            $table->date('resolution_date')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->timestamps();

            $table->foreign('complaint_from')->references('id')->on('staff_members')->onDelete('cascade');
            $table->foreign('complaint_against')->references('id')->on('staff_members')->onDelete('set null');
            $table->foreign('complaint_against_division')->references('id')->on('divisions')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
