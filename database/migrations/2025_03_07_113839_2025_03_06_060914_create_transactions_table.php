<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('txnId')->unique();
            $table->string('thirdPartyId')->nullable();
            $table->string('transactionType')->nullable();
            $table->uuid('merId');
            $table->string('merName');
            $table->string('address')->nullable();
            $table->decimal('amount', 10, 5);
            $table->decimal('commission', 10, 5);
            $table->decimal('totalAmount', 10, 5);
            $table->string('currency', 10);
            $table->text('reason')->nullable();
            $table->string('refundId')->nullable();
            $table->string('msisdn', 20)->nullable();
            $table->string('accountNumber')->nullable();
            $table->string('clientReference')->nullable();
            $table->string('paymentVia');
            $table->uuid('refId');
            $table->string('successRedirectUrl')->nullable();
            $table->string('failureRedirectUrl')->nullable();
            $table->string('cancelRedirectUrl')->nullable();
            $table->decimal('commissionAmountInPercent', 10, 5);
            $table->decimal('providerCommissionAmountInPercent', 10, 5);
            $table->boolean('commissionFromCustomer')->default(false);
            $table->decimal('vatAmountInPercent', 10, 5);
            $table->decimal('lotteryTax', 10, 5)->default(0);
            $table->text('message')->nullable();
            $table->string('updateType')->nullable();
            $table->string('Status');
            $table->text('StatusReason')->nullable();
            $table->string('ReceiverWalletID')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
