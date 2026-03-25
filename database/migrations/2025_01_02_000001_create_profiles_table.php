<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('nickname')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('date_of_death')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->text('bio')->nullable();
            // Privacy states for sensitive fields: public, private, masked
            $table->enum('phone_privacy',  ['public', 'private', 'masked'])->default('masked');
            $table->string('phone')->nullable();
            $table->enum('email_privacy',  ['public', 'private', 'masked'])->default('masked');
            $table->enum('dob_privacy',    ['public', 'private', 'masked'])->default('public');
            $table->enum('address_privacy',['public', 'private', 'masked'])->default('masked');
            $table->text('address')->nullable();
            // Father / Mother names for auto-graph linkage
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            // Neo4j node UUID
            $table->uuid('graph_node_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
