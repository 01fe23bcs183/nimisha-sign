<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_slips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_member_id');
            $table->decimal('net_payable', 15, 2)->default(0);
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->string('salary_month');
            $table->enum('status', ['draft', 'generated', 'paid'])->default('draft');
            $table->json('allowance')->nullable();
            $table->json('commission')->nullable();
            $table->json('loan')->nullable();
            $table->json('saturation_deduction')->nullable();
            $table->json('other_payment')->nullable();
            $table->json('overtime')->nullable();
            $table->json('company_contribution')->nullable();
            $table->json('tax_bracket')->nullable();
            $table->decimal('total_allowance', 15, 2)->default(0);
            $table->decimal('total_commission', 15, 2)->default(0);
            $table->decimal('total_loan', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('total_other_payment', 15, 2)->default(0);
            $table->decimal('total_overtime', 15, 2)->default(0);
            $table->decimal('total_company_contribution', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('gross_salary', 15, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->timestamps();

            $table->foreign('staff_member_id')->references('id')->on('staff_members')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_slips');
    }
};
