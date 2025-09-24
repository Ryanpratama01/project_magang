<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;  // Pastikan menggunakan 'Illuminate\Support\Facades\DB'
 // Menggunakan DB untuk Query Builder
// Gunakan model jika dibutuhkan, jika tidak cukup menggunakan DB::table()

class SentimentController extends Controller
{
    public function index()
    {
        $batchStats = [
            'total' => 24,
            'analyzed' => 18,
            'pending' => 6
        ];
        
        // Mengambil data dari database
        $newsSentiments = DB::table('news_article_sentiments')
    ->select('article_idx', 'article_fk', 'quote_text', 'label', 'score_positive', 'score_negative', 'score_neutral')
    ->get();


        
        // Mengirim data ke view
        return view('sentiment', compact('batchStats', 'newsSentiments'));
    }
    
    public function analyze(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:10',
            'model' => 'nullable|string|in:bert,lstm,naive_bayes',
            'language' => 'nullable|string|in:id,en'
        ]);
        
        // Simulasi analisis sentimen
        $sentiments = ['positive', 'negative', 'neutral'];
        $randomSentiment = $sentiments[array_rand($sentiments)];
        $confidence = rand(70, 100);
        
        // Extract keywords (simplified)
        $words = explode(' ', $request->text);
        $keywords = array_slice($words, 0, min(5, count($words)));
        
        $result = [
            'text' => $request->text,
            'sentiment' => $randomSentiment,
            'confidence' => $confidence,
            'model' => $request->model ?? 'bert',
            'language' => $request->language ?? 'id',
            'keywords' => $keywords,
            'processed_at' => now(),
            'processing_time' => rand(100, 500) . 'ms'
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Analysis completed successfully',
            'data' => $result
        ]);
    }
}
