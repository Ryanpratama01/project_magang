<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('batches', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->index();
            $t->string('source_type')->default('file_upload'); // file_upload | api | manual
            $t->string('file_path')->nullable();
            $t->string('original_filename')->nullable();
            $t->unsignedInteger('batch_size')->default(25);
            $t->string('priority')->default('default'); // high|default|low
            $t->unsignedInteger('total_items')->default(0);
            $t->unsignedInteger('processed_items')->default(0);
            $t->enum('status', ['created','running','paused','done','failed'])->default('created');
            $t->timestamps();
        });

        Schema::create('batch_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $t->text('payload')->nullable();     // URL/teks mentah (string/JSON)
            $t->string('hash')->index();         // sha1(payload) untuk dedup
            $t->enum('status', ['pending','queued','processing','success','failed'])->default('pending');
            $t->json('result')->nullable();      // ringkasan hasil
            $t->text('error_message')->nullable();
            $t->timestamp('queued_at')->nullable();
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();

            $t->unique(['batch_id','hash']);     // cegah item ganda di batch yang sama
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::dropIfExists('batch_items');
        Schema::dropIfExists('batches');
    }
};
