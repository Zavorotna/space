<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->foreignId('activation_topic_id')
                ->nullable()->constrained('course_topics')->nullOnDelete()
                ->after('sort_order');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->json('topic_ids')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', fn(Blueprint $t) => $t->dropColumn('topic_ids'));
        Schema::table('tests', function (Blueprint $t) {
            $t->dropForeign(['activation_topic_id']);
            $t->dropColumn('activation_topic_id');
        });
        Schema::dropIfExists('course_topics');
    }
};
