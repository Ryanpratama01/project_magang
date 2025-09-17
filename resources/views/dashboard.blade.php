@extends('layouts.app')

@section('title', 'Dashboard - Sistem Analisis Sentimen')
@section('page-title', 'Dashboard')
@section('page-description', 'Ringkasan analisis sentimen dan statistik sistem')

@section('content')
<div class="space-y-8">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Berita</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total_news'] ?? '1,234' }}</p>
                    <p class="text-green-500 text-sm">+12% dari bulan lalu</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-newspaper text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Sentimen Positif</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['positive'] ?? '68%' }}</p>
                    <p class="text-green-500 text-sm">+5% dari minggu lalu</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-smile text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Sentimen Negatif</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['negative'] ?? '22%' }}</p>
                    <p class="text-red-500 text-sm">-3% dari minggu lalu</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-frown text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Akurasi Model</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['accuracy'] ?? '94.5%' }}</p>
                    <p class="text-blue-500 text-sm">Model terlatih</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bullseye text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Sentiment Trend Chart -->
        <div class="bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">Trend Sentimen</h3>
                <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option>7 Hari Terakhir</option>
                    <option>30 Hari Terakhir</option>
                    <option>3 Bulan Terakhir</option>
                </select>
            </div>
            <div class="relative h-80">
                <canvas id="sentimentChart"></canvas>
            </div>
        </div>
        
        <!-- Sentiment Distribution -->
        <div class="bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">Distribusi Sentimen</h3>
                <div class="flex space-x-2">
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full">Positif</span>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Netral</span>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-xs rounded-full">Negatif</span>
                </div>
            </div>
            <div class="relative h-80">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity & Top Keywords -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent News -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">Berita Terbaru</h3>
                <a href="{{ route('scraping') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Lihat Semua</a>
            </div>
            <div class="space-y-4">
                @for($i = 1; $i <= 5; $i++)
                <div class="flex items-start space-x-4 p-4 hover:bg-gray-50 rounded-xl transition-colors">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800 mb-1">Berita Sample {{ $i }}</h4>
                        <p class="text-gray-600 text-sm mb-2">Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Positif</span>
                            <span class="text-gray-500 text-xs">{{ now()->subHours($i)->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
        
        <!-- Top Keywords -->
        <div class="bg-white rounded-2xl p-6 card-shadow">
            <h3 class="text-xl font-bold text-gray-800 mb-6">Kata Kunci Populer</h3>
            <div class="space-y-4">
                @php
                $keywords = [
                    ['word' => 'ekonomi', 'count' => 234, 'sentiment' => 'positive'],
                    ['word' => 'politik', 'count' => 189, 'sentiment' => 'negative'],
                    ['word' => 'teknologi', 'count' => 167, 'sentiment' => 'positive'],
                    ['word' => 'kesehatan', 'count' => 143, 'sentiment' => 'neutral'],
                    ['word' => 'pendidikan', 'count' => 132, 'sentiment' => 'positive'],
                ];
                @endphp
                
                @foreach($keywords as $keyword)
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-{{ $keyword['sentiment'] === 'positive' ? 'green' : ($keyword['sentiment'] === 'negative' ? 'red' : 'yellow') }}-100 rounded-lg flex items-center justify-center">
                            <span class="text-{{ $keyword['sentiment'] === 'positive' ? 'green' : ($keyword['sentiment'] === 'negative' ? 'red' : 'yellow') }}-600 text-xs font-bold">#</span>
                        </div>
                        <span class="font-medium text-gray-800">{{ $keyword['word'] }}</span>
                    </div>
                    <span class="text-gray-600 text-sm">{{ $keyword['count'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Sentiment Trend Chart
    const sentimentCtx = document.getElementById('sentimentChart').getContext('2d');
    new Chart(sentimentCtx, {
        type: 'line',
        data: {
            labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            datasets: [{
                label: 'Positif',
                data: [65, 68, 72, 71, 75, 73, 78],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Negatif',
                data: [25, 22, 18, 19, 15, 17, 12],
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Netral',
                data: [10, 10, 10, 10, 10, 10, 10],
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
    
    // Pie Chart
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Positif', 'Netral', 'Negatif'],
            datasets: [{
                data: [68, 10, 22],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush