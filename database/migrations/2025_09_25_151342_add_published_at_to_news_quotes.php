<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_published_at_to_news_quotes.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('news_quotes', function (Blueprint $t) {
            $t->dateTime('published_at')->nullable()->after('context');
            // pastikan kolom waktu lain tidak auto default dari DB
            $t->dateTime('scraped_at')->nullable()->change();
            $t->dateTime('created_at')->nullable()->change();
            $t->dateTime('updated_at')->nullable()->change();
            $t->index('published_at');
        });
    }

    public function down(): void {
        Schema::table('news_quotes', function (Blueprint $t) {
            $t->dropIndex(['published_at']);
            $t->dropColumn('published_at');
        });
    }
};
