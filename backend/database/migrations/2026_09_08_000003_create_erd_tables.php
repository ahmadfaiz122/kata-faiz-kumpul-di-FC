<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('profiles', 'rating')) {
                $table->decimal('rating', 3, 2)->default(0)->after('bio');
            }
            if (! Schema::hasColumn('profiles', 'reputation')) {
                $table->unsignedInteger('reputation')->default(0)->after('rating');
            }
            if (! Schema::hasColumn('profiles', 'leaderboard')) {
                $table->unsignedInteger('leaderboard')->default(0)->after('reputation');
            }
            if (! Schema::hasColumn('profiles', 'credits')) {
                $table->unsignedInteger('credits')->default(0)->after('twitter');
            }
            if (! Schema::hasColumn('profiles', 'social_media')) {
                $table->json('social_media')->nullable()->after('credits');
            }
            if (! Schema::hasColumn('profiles', 'photo_profile')) {
                $table->string('photo_profile')->nullable()->after('social_media');
            }
        });

        if (! Schema::hasTable('skills')) {
            Schema::create('skills', function (Blueprint $table) {
                $table->id();
                $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
                $table->string('name');
                $table->string('category_skills')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('achievements')) {
            Schema::create('achievements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
                $table->string('name');
                $table->string('levels')->nullable();
                $table->string('category')->nullable();
                $table->boolean('validated_upload')->default(false);
                $table->integer('tanggal_terbit');
                $table->integer('kadaluwarsa');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('post_timelines')) {
            Schema::create('post_timelines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
                $table->text('captions');
                $table->text('comment')->nullable();
                $table->unsignedInteger('likes')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('requests')) {
            Schema::create('requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('skill_id')->nullable()->constrained('skills')->nullOnDelete();
                $table->foreignId('requester')->constrained('users')->cascadeOnDelete();
                $table->string('new_column')->nullable();
                $table->string('status')->default('pending');
                $table->unsignedInteger('hour')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('barter_request')->nullable()->constrained('requests')->nullOnDelete();
                $table->foreignId('provider')->constrained('users')->cascadeOnDelete();
                $table->foreignId('requester')->constrained('users')->cascadeOnDelete();
                $table->unsignedInteger('hour')->default(1);
                $table->unsignedInteger('credits')->default(0);
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('requests');
        Schema::dropIfExists('post_timelines');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('skills');
    }
};