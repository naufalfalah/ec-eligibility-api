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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('household')->nullable();
            $table->string('citizenship')->nullable();
            $table->string('requirement')->nullable();
            $table->string('household_income')->nullable();
            $table->string('ownership_status')->nullable();
            $table->string('private_property_ownership')->nullable();
            $table->string('first_time_applicant')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->datetime('verified_at')->nullable();
            $table->boolean('send_discord')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
