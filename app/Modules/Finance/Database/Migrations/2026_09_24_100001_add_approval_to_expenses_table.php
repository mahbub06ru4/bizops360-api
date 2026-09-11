<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->string('status')->default('pending')->after('note');
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('decision_note')->nullable()->after('approved_at');

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['status', 'approved_at', 'decision_note']);
        });
    }
};
