<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScrapingController extends Controller
{
    public function index()
    {
        return view('scraping');
    }
    
    public function startScraping(Request $request)
    {
        $request->validate([
            'links' => 'required|string'
        ]);
        
        // Process scraping logic here
        $links = explode("\n", $request->links);
        $results = [];
        
        foreach ($links as $link) {
            $link = trim($link);
            if (!empty($link)) {
                // Simulate scraping result
                $results[] = [
                    'url' => $link,
                    'title' => 'Sample News Title from ' . parse_url($link, PHP_URL_HOST),
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...',
                    'published_at' => now(),
                    'status' => 'success'
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Scraping completed successfully',
            'data' => $results,
            'total' => count($results)
        ]);
    }
    
    public function searchNews(Request $request)
    {
        $request->validate([
            'keywords' => 'required|string',
            'source' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date'
        ]);
        
        // Simulate search and scraping
        $results = [];
        for ($i = 1; $i <= 10; $i++) {
            $results[] = [
                'id' => $i,
                'title' => 'Search Result ' . $i . ' - ' . $request->keywords,
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'source' => $request->source ?? 'detik.com',
                'url' => 'https://example.com/news/' . $i,
                'published_at' => now()->subDays(rand(1, 7)),
                'status' => 'success'
            ];
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Search completed successfully',
            'data' => $results,
            'total' => count($results)
        ]);
    }
}
