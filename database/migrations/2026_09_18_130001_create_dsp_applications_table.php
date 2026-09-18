<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dsp_applications', function (Blueprint $table) {
            $table->id();
            
            // Header / Top Meta Fields
            $table->string('application_number')->unique();
            $table->date('application_date')->nullable();
            $table->string('preferred_territory_area')->nullable();
            $table->string('pincodes')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();

            // 1. Applicant Details
            $table->string('applicant_name');
            $table->string('father_or_spouse_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('mobile', 20);
            $table->string('whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('residential_address')->nullable();
            $table->string('residential_pincode', 10)->nullable();

            // 2. Business Details
            $table->string('business_name');
            $table->string('business_constitution')->default('proprietorship'); // proprietorship, partnership, llp, private_limited, other
            $table->string('business_constitution_other')->nullable();
            $table->string('year_established', 10)->nullable();
            $table->string('pan', 20)->nullable();
            $table->string('gstin', 30)->nullable();
            $table->string('existing_business_activity')->nullable();
            $table->string('years_of_experience', 10)->nullable();

            // 3. Proposed DSP Location
            $table->string('premises_type')->default('owned'); // owned, rented, leased
            $table->text('complete_address')->nullable();
            $table->string('premises_pincode', 10)->nullable();
            $table->decimal('total_area_sqft', 10, 2)->nullable();
            $table->decimal('frontage_feet', 8, 2)->nullable();
            $table->json('available_facilities')->nullable(); // product_storage, cctv, customer_reception, fire_safety, service_workshop, secure_storage, electricity, internet, parking

            // 4. Service Capability
            $table->boolean('presently_operate_service_centre')->default(false);
            $table->integer('technicians_count')->default(0);
            $table->string('ev_technician_status')->default('no'); // yes, no, will_recruit
            $table->string('electrical_technician_status')->default('no'); // yes, no, will_recruit
            $table->string('home_appliance_technician_status')->default('no'); // yes, no, will_recruit
            $table->boolean('agree_to_nexvia_training')->default(true);

            // 5. Products & Delivery Capability
            $table->json('products_handled')->nullable(); // electric_scooters, smart_tvs, refrigerators, washing_machines, air_conditioners, mixer_grinders, gas_stoves, kitchen_chimneys, ovens, electric_irons, speakers_audio, all_approved
            $table->integer('vehicles_two_wheeler')->default(0);
            $table->integer('vehicles_three_wheeler')->default(0);
            $table->integer('vehicles_pickup_lcv')->default(0);
            $table->string('vehicles_other')->nullable();
            $table->decimal('max_delivery_radius_km', 8, 2)->nullable();
            $table->boolean('pdi_handover_sop')->default(true);
            $table->boolean('otp_delivery_confirmation')->default(true);

            // 6. Security Stock Deposit (₹10,00,000 against company inventory)
            $table->decimal('security_deposit_amount', 14, 2)->default(1000000.00);
            $table->boolean('deposit_terms_agreed')->default(true);
            $table->string('deposit_payment_status')->default('pending'); // pending, paid, verified
            $table->string('deposit_transaction_reference')->nullable();
            $table->string('deposit_payment_proof')->nullable();

            // 8. Applicant Declaration & Signoff
            $table->boolean('declaration_agreed')->default(true);
            $table->date('declaration_date')->nullable();
            $table->string('declaration_signature_name')->nullable();
            $table->string('signature_file')->nullable();

            // Admin Status & Review
            $table->string('status')->default('pending'); // pending, under_review, approved, rejected
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dsp_applications');
    }
};
