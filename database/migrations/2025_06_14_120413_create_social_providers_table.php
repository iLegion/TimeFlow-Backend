<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('provider_id');
            $table->string('provider_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_providers');
    }
};
