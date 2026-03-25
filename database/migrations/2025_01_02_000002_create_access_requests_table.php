<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('target_id')->constrained('users')->onDelete('cascade');
            // Which data points are being requested (e.g. ['phone','address'])
            $table->json('requested_fields');
            $table->enum('status', ['pending', 'approved', 'rejected', 'revoked'])->default('pending');
            $table->text('requester_message')->nullable();
            $table->text('target_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            // A requester can only have one active request per target
            $table->unique(['requester_id', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_requests');
    }
};
