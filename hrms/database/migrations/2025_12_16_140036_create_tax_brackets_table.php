<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_brackets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('from_amount', 15, 2)->default(0);
            $table->decimal('to_amount', 15, 2)->default(0);
            $table->decimal('fixed_amount', 15, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_brackets');
    }
};
