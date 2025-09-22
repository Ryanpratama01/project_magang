<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Sistem Analisis Sentimen</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        .otp-card {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .otp-input {
            width: 3.5rem;
            height: 3.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            background: white;
            transition: all 0.2s ease;
            outline: none;
        }
        .otp-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }
        .otp-input:hover {
            border-color: #6b7280;
        }
        .bounce-in {
            animation: bounceIn 0.6s ease-out;
        }
        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="otp-card rounded-3xl p-8 w-full max-w-md bounce-in">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-700 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                <i class="fas fa-shield-alt text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent mb-3">
                Verifikasi OTP
            </h1>
            <p class="text-gray-600 text-center leading-relaxed">
                Masukkan kode OTP yang telah dikirim ke email<br>
                <strong class="text-blue-600">{{ session('reset_email') }}</strong>
            </p>
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

        <!-- OTP Form -->
        <form method="POST" action="{{ route('password.verify.otp.post') }}" class="space-y-6" id="otpForm">
            @csrf
            
            <!-- OTP Input Section -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-2xl">
                <label class="block text-sm font-semibold text-gray-700 mb-6 text-center flex items-center justify-center">
                    <i class="fas fa-key text-blue-600 mr-2"></i>
                    Masukkan Kode OTP (6 digit)
                </label>
                
                <div class="flex justify-center space-x-3 mb-4">
                    <input type="text" id="otp1" maxlength="1" class="otp-input">
                    <input type="text" id="otp2" maxlength="1" class="otp-input">
                    <input type="text" id="otp3" maxlength="1" class="otp-input">
                    <input type="text" id="otp4" maxlength="1" class="otp-input">
                    <input type="text" id="otp5" maxlength="1" class="otp-input">
                    <input type="text" id="otp6" maxlength="1" class="otp-input">
                </div>
                <input type="hidden" name="otp" id="otpHidden">
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl hover:from-blue-600 hover:to-blue-800 transition-all duration-300 font-semibold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                    id="verifyBtn">
                <i class="fas fa-check-circle mr-2"></i>Verifikasi OTP
            </button>
        </form>

        <!-- Resend Section -->
        <div class="mt-8">
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 p-4 rounded-xl text-center">
                <p class="text-sm text-gray-600 mb-3 flex items-center justify-center">
                    <i class="fas fa-clock mr-2 text-blue-500"></i>
                    Tidak menerima kode OTP?
                </p>
                <form method="POST" action="{{ route('password.resend.otp') }}" id="resendForm">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-white text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 font-medium text-sm shadow-sm"
                            id="resendBtn">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Kirim Ulang OTP
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                © 2025 Sistem Analisis Sentimen. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpHidden = document.getElementById('otpHidden');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
                updateHiddenOTP();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            // Add paste support
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = e.clipboardData.getData('text');
                const digits = paste.replace(/[^0-9]/g, '').slice(0, 6);
                
                for (let i = 0; i < digits.length && (index + i) < otpInputs.length; i++) {
                    otpInputs[index + i].value = digits[i];
                }
                updateHiddenOTP();
                
                // Focus next empty input or last input
                const nextIndex = Math.min(index + digits.length, otpInputs.length - 1);
                otpInputs[nextIndex].focus();
            });
        });

        function updateHiddenOTP() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            otpHidden.value = otp;
        }

        document.getElementById('otpForm').addEventListener('submit', function(e) {
            const verifyBtn = document.getElementById('verifyBtn');
            const otp = otpHidden.value;

            if (otp.length !== 6) {
                e.preventDefault();
                alert('Mohon masukkan semua 6 digit kode OTP!');
                otpInputs[0].focus();
                return;
            }

            verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memverifikasi...';
            verifyBtn.disabled = true;
        });

        document.getElementById('resendForm').addEventListener('submit', function(e) {
            const resendBtn = document.getElementById('resendBtn');
            resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';
            resendBtn.disabled = true;

            // Reset after 30 seconds
            setTimeout(() => {
                resendBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Kirim Ulang OTP';
                resendBtn.disabled = false;
            }, 30000);
        });

        // Auto focus first input on page load
        window.addEventListener('load', function() {
            otpInputs[0].focus();
        });
    </script>
</body>
</html>