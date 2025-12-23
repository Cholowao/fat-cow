<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - creates transactions table for tracking money flow
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Owner of transaction
            $table->enum('type', ['credit', 'debit']); // credit = money received, debit = expense
            $table->decimal('amount', 12, 2); // Transaction amount
            $table->string('description'); // What the transaction is for
            $table->string('category')->nullable(); // Optional category (meat, transport, etc)
            $table->date('transaction_date'); // Date of transaction
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
