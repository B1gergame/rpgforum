<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // actors
        Schema::create('actors', function (Blueprint $table) {
            $table->id();
            // string instead of enum for portability between SQLite/PG
            $table->string('kind'); // player_character|gm_character|npc|system
            $table->string('name');
            $table->string('avatar_url')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('system_code')->nullable(); // narrator|dice|modbot
            $table->timestamps();

            $table->index(['kind']);
            $table->index(['owner_user_id']);
        });

        // actor_memberships (who can post as actor)
        Schema::create('actor_memberships', function (Blueprint $table) {
            $table->foreignId('actor_id')->constrained('actors')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role'); // owner|editor|poster
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['actor_id', 'user_id']);
            $table->index(['user_id']);
        });

        // scene_actors (access of actor in scene)
        Schema::create('scene_actors', function (Blueprint $table) {
            $table->foreignId('scene_id')->constrained('scenes')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('actors')->cascadeOnDelete();
            $table->string('visibility')->default('participants'); // participants|gm_only|public
            $table->boolean('can_post')->default(true);
            $table->text('note')->nullable();

            $table->primary(['scene_id','actor_id']);
            $table->index(['scene_id','visibility']);
        });

        // posts: add relation to actor and actual user
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('actor_id')->nullable()->after('id')->constrained('actors')->nullOnDelete();
            $table->foreignId('posted_by_user_id')->nullable()->after('actor_id')->constrained('users')->nullOnDelete();
            $table->index(['scene_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('actor_id');
            $table->dropConstrainedForeignId('posted_by_user_id');
            $table->dropIndex('posts_scene_id_created_at_index');
        });
        Schema::dropIfExists('scene_actors');
        Schema::dropIfExists('actor_memberships');
        Schema::dropIfExists('actors');
    }
};
