<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('edrpou', 8)->unique();
            $table->string('bank_account', 34);
            $table->string('contact_email');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_providers');
    }
};
