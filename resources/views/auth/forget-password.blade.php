<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Sistem Analisis Sentimen</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        .forgot-card {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .floating-label {
            position: relative;
        }
        .floating-input {
            padding-top: 1.25rem;
            padding-bottom: 0.25rem;
        }
        .floating-input + label {
            position: absolute;
            top: 0.75rem;
            left: 3rem;
            color: #9ca3af;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            pointer-events: none;
        }
        .floating-input:focus + label,
        .floating-input:not(:placeholder-shown) + label {
            top: 0.25rem;
            font-size: 0.75rem;
            color: #3b82f6;
        }
        .input-group {
            position: relative;
            overflow: hidden;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }
        .input-group:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="forgot-card rounded-3xl p-8 w-full max-w-md">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-700 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                <img src="{{ asset('assets/images/logoDPU.png') }}" alt="Logo DPU" class="w-12 h-12 object-contain">
            </div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent mb-3">Lupa Password?</h1>
            <p class="text-gray-600">Masukkan email Anda dan kami akan mengirimkan kode OTP untuk reset password</p>
        </div>
        
        <!-- Error Messages -->
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-lg">
                @foreach ($errors->all() as $error)
                    <div class="flex items-center mb-2 last:mb-0">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <span class="text-red-700 text-sm">{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif
        
        <!-- Success Message -->
        @if (session('status'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span class="text-green-700 text-sm">{{ session('status') }}</span>
                </div>
            </div>
        @endif
        
        <!-- Forgot Password Form -->
        <form method="POST" action="{{ route('password.email') }}" class="space-y-6" id="forgotForm">
            @csrf
            
            <!-- Email Section -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-2xl">
                <div class="floating-label">
                    <div class="input-group">
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               required 
                               autofocus
                               placeholder=" "
                               class="floating-input w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
                        <label for="email">Alamat Email</label>
                        <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400"></i>
                    </div>
                </div>
                <p class="text-xs text-blue-600 mt-3 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    Pastikan email yang Anda masukkan sudah terdaftar di sistem
                </p>
            </div>
            
            <button type="submit" 
                    class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl hover:from-blue-600 hover:to-blue-800 transition-all duration-300 font-semibold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                    id="submitBtn">
                <i class="fas fa-paper-plane mr-2"></i>Kirim Kode OTP
            </button>
        </form>
        
        <!-- Alternative Actions -->
        <div class="mt-8 space-y-4">
            <div class="flex items-center">
                <div class="flex-1 border-t border-gray-300"></div>
                <span class="px-4 text-sm text-gray-500">atau</span>
                <div class="flex-1 border-t border-gray-300"></div>
            </div>
            
            <div class="text-center space-y-3">
                <div class="inline-flex items-center px-6 py-3 bg-white rounded-full shadow-md">
                    <span class="text-gray-600 mr-2">Sudah ingat password?</span>
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                        <i class="fas fa-sign-in-alt mr-1"></i>Masuk di sini
                    </a>
                </div>
                
                <div class="inline-flex items-center px-6 py-3 bg-white rounded-full shadow-md">
                    <span class="text-gray-600 mr-2">Belum punya akun?</span>
                    <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                        <i class="fas fa-user-plus mr-1"></i>Daftar sekarang
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Help Section -->
        {{-- <div class="mt-8 bg-gradient-to-r from-indigo-50 to-purple-50 p-6 rounded-2xl">
            <div class="flex items-start space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-question-circle text-white"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-blue-800 mb-2">Butuh Bantuan?</h4>
                    <p class="text-sm text-blue-700 mb-3">Jika Anda mengalami kesulitan:</p>
                    <div class="space-y-2">
                        <div class="flex items-center text-sm text-blue-600">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            Pastikan email sudah terdaftar di sistem
                        </div>
                        <div class="flex items-center text-sm text-blue-600">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            Periksa folder spam/junk email Anda
                        </div>
                        <div class="flex items-center text-sm text-blue-600">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            Tunggu hingga 5 menit untuk menerima kode OTP
                        </div>
                        <div class="flex items-center text-sm text-blue-600">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            Hubungi administrator jika masalah berlanjut
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
        
        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                © 2025 Sistem Analisis Sentimen. All rights reserved.
            </p>
        </div>
    </div>
    
    <script>
        // Form submission with loading state
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const email = document.getElementById('email').value;
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Mohon masukkan alamat email yang valid!');
                return;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';
            submitBtn.disabled = true;
            
            // Re-enable button after 30 seconds (in case of failure)
            setTimeout(() => {
                submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Kirim Kode OTP';
                submitBtn.disabled = false;
            }, 30000);
        });
        
        // Auto-focus on email field
        document.getElementById('email').focus();
    </script>
</body>
</html>