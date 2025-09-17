<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_news' => 1234,
            'positive' => '68%',
            'negative' => '22%',
            'accuracy' => '94.5%'
        ];
        
        return view('dashboard', compact('stats'));
    }
}
