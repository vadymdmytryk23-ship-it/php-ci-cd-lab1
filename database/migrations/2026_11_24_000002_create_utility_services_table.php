<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->cascadeOnDelete();
            $table->index('service_provider_id');
            $table->string('name');
            $table->string('unit');
            $table->decimal('tariff', 10, 2);
            $table->date('active_from');
            $table->timestamps();
            $table->unique(['service_provider_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_services');
    }
};
