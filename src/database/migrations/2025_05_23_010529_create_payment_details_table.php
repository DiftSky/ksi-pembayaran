<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('method_id')->constrained('payment_methods')->onDelete('cascade');
            $table->text('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('card_type')->nullable();
            $table->text('last_four_digits')->nullable(); // Changed from string(4) to text to accommodate encrypted values
            $table->date('expiry_date')->nullable();
            $table->text('holder_name')->nullable(); // Changed to text for encrypted values
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_details');
    }
};
