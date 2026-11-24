<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_readings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('utility_account_id')->constrained()->cascadeOnDelete();
            $table->decimal('previous_value', 12, 3);
            $table->decimal('current_value', 12, 3);
            $table->timestamp('submitted_at')->useCurrent();
            $table->string('status')->default('submitted');
            $table->timestamps();
            $table->index(['utility_account_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
