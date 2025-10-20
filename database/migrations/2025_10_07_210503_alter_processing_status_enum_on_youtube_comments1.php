<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // normalisasi nilai lama agar aman
        DB::table('youtube_comments1')
          ->whereNull('processing_status')
          ->update(['processing_status' => 'scraped']);

        DB::statement("
            ALTER TABLE `youtube_comments1`
            MODIFY `processing_status`
            ENUM('pending','processing','scraped','completed','failed')
            NOT NULL DEFAULT 'scraped'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `youtube_comments1`
            MODIFY `processing_status`
            ENUM('pending','processing','completed','failed')
            NOT NULL DEFAULT 'completed'
        ");
    }
};
