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
        Schema::create('transaction-details', function (Blueprint $table) {
            $table->id();
            $table->integer('transanction_id');
            $table->integer('coa_from');
            $table->decimal('debit');
            $table->decimal('credit');
            $table->integer('coa_to');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction-details');
    }
};
