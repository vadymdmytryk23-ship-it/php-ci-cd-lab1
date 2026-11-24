<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_account_utility_service', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('utility_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('utility_service_id')->constrained()->cascadeOnDelete();
            $table->index('utility_service_id');
            $table->timestamps();
            $table->unique(['utility_account_id', 'utility_service_id'], 'account_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_account_utility_service');
    }
};
