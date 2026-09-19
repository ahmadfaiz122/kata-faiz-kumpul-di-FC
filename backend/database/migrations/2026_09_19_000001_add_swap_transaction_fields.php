<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('profiles', 'credits')) {
                $table->unsignedInteger('credits')->default(1);
            }
        });

        DB::table('profiles')->where('credits', 0)->update(['credits' => 1]);

        Schema::table('requests', function (Blueprint $table) {
            if (! Schema::hasColumn('requests', 'available_at')) {
                $table->dateTime('available_at')->nullable()->after('hour');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'requester_skill_id')) {
                $table->foreignId('requester_skill_id')->nullable()->constrained('skills')->nullOnDelete()->after('requester');
            }
            if (! Schema::hasColumn('transactions', 'mode')) {
                $table->string('mode')->default('credit')->after('requester_skill_id');
            }
            if (! Schema::hasColumn('transactions', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('transactions', 'starts_at')) {
                $table->dateTime('starts_at')->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('transactions', 'ends_at')) {
                $table->dateTime('ends_at')->nullable()->after('starts_at');
            }
            if (! Schema::hasColumn('transactions', 'completed_at')) {
                $table->dateTime('completed_at')->nullable()->after('ends_at');
            }
        });

        Schema::create('transaction_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->unique()->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('reviewer')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('reputation')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_reviews');
        Schema::table('transactions', function (Blueprint $table) {
            foreach (['requester_skill_id', 'mode', 'approved_at', 'starts_at', 'ends_at', 'completed_at'] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        Schema::table('requests', function (Blueprint $table) {
            if (Schema::hasColumn('requests', 'available_at')) {
                $table->dropColumn('available_at');
            }
        });
    }
};
