<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('merchant_id');
            // TODO: Replace me with a brief explanation of why floats aren't the correct data type, and replace with the correct data type.
            $table->decimal('commission_rate', 10, 2);
            $table->string('discount_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Answer: 
     * Floating-point numbers in most programming languages, including PHP, use a binary representation to store fractional numbers. 
     * However, not all decimal numbers can be precisely represented in binary form. 
     * This can lead to rounding errors and inaccuracies, especially when performing arithmetic operations on these values.
     * For example, consider the following code snippet in PHP:
     * $commission_rate = 0.1 + 0.2;
     * You might expect the output to be 0.3, but due to floating-point imprecision, 
     * the actual output could be something like 0.30000000000000004. 
     * Such small discrepancies might not be critical in many cases, 
     * but when dealing with financial values, even tiny inaccuracies can accumulate and lead to significant errors in calculations.
     */

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliates');
    }
};
