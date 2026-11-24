<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('utility_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('utility_service_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7);
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->string('status')->default('unpaid');
            $table->timestamps();
            $table->unique(['utility_account_id', 'utility_service_id', 'period']);
            $table->index(['utility_account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
