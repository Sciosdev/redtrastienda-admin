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
        Schema::create('opportunity_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('seller_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->text('comment')->nullable();
            $table->string('status')->default('new');
            $table->text('provider_response')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->index('product_id');
            $table->index('seller_id');
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunity_requests');
    }
};
