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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('current_step')->default('programme');
            $table->string('payment_status')->default('unpaid');
            $table->json('completed_steps')->nullable();
            $table->string('application_no')->unique();
            $table->string('school')->nullable();
            $table->string('discipline')->nullable();
            $table->string('specialization')->nullable();
            $table->string('specialization_other')->nullable();
            $table->json('declaration')->nullable();
            $table->enum('programme_mode', [
                'full_time',
                'part_time'
            ]);
            $table->string('photo')->nullable();
            $table->string('full_name');
            $table->date('dob');
            $table->unsignedTinyInteger('age')->nullable();

            $table->enum('gender', [
                'Male',
                'Female',
                'Transgender',
                'Other'
            ]);

            $table->boolean('single_girl_child')->default(false);

            $table->string('nationality')->default('Indian');
            $table->string('religion');

            $table->string('community')->nullable();
            $table->string('community_certificate')->nullable();

            $table->boolean('differently_abled')->default(false);
            $table->string('disability_certificate')->nullable();

            $table->string('father_name');
            $table->string('mother_name');

            $table->string('mobile', 20);
            $table->string('email')->unique();

            $table->text('address_current');
            $table->boolean('address_same')->default(false);
            $table->text('address_permanent');

            // Eligibility
            $table->boolean('eligibility_qualified')->default(false);
            $table->string('eligibility_exam')->nullable();
            $table->string('eligibility_certificate')->nullable();

            // Service Summary
            $table->unsignedInteger('total_service_years')->default(0);
            $table->unsignedInteger('total_service_months')->default(0);

            // Career
            $table->string('career_other')->nullable();

            // Research Summary
            $table->string('summary_document')->nullable();

            // Enclosures
            $table->string('noc_document')->nullable();
            $table->string('service_certificate')->nullable();
            $table->string('equivalence_certificate')->nullable();

            $table->boolean('enclosures_confirm')->default(false);

            // Workflow
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'rejected'
            ])->default('draft');

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('application');
    }
};
