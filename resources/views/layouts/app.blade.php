<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Analisis Sentimen')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .hover-scale {
            transition: transform 0.2s;
        }
        .hover-scale:hover {
            transform: scale(1.02);
        }
        .sidebar-active {
            background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="h-full font-sans overflow-hidden">
    <div class="flex h-full">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-xl border-r border-gray-200">
            <div class="p-6">
                <!-- Logo -->
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 flex items-center justify-center">
                        <img src="{{ asset('images/logoDPU.png') }}" alt="Logo DPU" class="h-full w-full object-contain">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Sistem</h1>
                        <p class="text-sm text-gray-600">Analisis Sentimen</p>
                    </div>
                </div>
                
                <!-- Navigation Menu -->
                <nav class="space-y-2">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-blue-50 {{ request()->routeIs('dashboard') ? 'sidebar-active text-white' : 'text-gray-700 hover:text-blue-600' }}">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    
                    <a href="{{ route('scraping') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-blue-50 {{ request()->routeIs('scraping') ? 'sidebar-active text-white' : 'text-gray-700 hover:text-blue-600' }}">
                        <i class="fas fa-spider w-5"></i>
                        <span class="font-medium">Scraping</span>
                    </a>
                    
                    <a href="{{ route('sentiment') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-blue-50 {{ request()->routeIs('sentiment') ? 'sidebar-active text-white' : 'text-gray-700 hover:text-blue-600' }}">
                        <i class="fas fa-brain w-5"></i>
                        <span class="font-medium">Analisis Sentimen</span>
                    </a>
                    
                    <a href="{{ route('report') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-blue-50 {{ request()->routeIs('report') ? 'sidebar-active text-white' : 'text-gray-700 hover:text-blue-600' }}">
                        <i class="fas fa-file-alt w-5"></i>
                        <span class="font-medium">Cetak Laporan</span>
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}" class="mt-8">
                        @csrf
                        <button type="submit" 
                                class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 text-red-600 hover:bg-red-50 w-full text-left">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span class="font-medium">Logout</span>
                        </button>
                    </form>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-8 py-4 flex-shrink-0">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">@yield('page-title')</h2>
                        <p class="text-gray-600">@yield('page-description')</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors">
                                <i class="fas fa-bell text-gray-600"></i>
                            </button>
                        </div>
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-medium">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 p-8 animate-fade-in overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // CSRF token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Animation on load
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.animate-fade-in');
            elements.forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>