<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Sistem Analisis Sentimen</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        .reset-card {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
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
    <div class="reset-card rounded-3xl p-8 w-full max-w-md bounce-in">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-700 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                <i class="fas fa-key text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent mb-3">
                Reset Password
            </h1>
            <p class="text-gray-600 text-center leading-relaxed">
                Buat password baru untuk akun<br>
                <strong class="text-blue-600">{{ request('email') }}</strong>
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

        <!-- Success Message -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span class="text-green-700 text-sm">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Reset Password Form -->
       <form method="POST" action="{{ route('password.update') }}" id="resetForm" class="space-y-6">
    @csrf
    <!-- Hidden Fields -->
    <input type="hidden" name="email" value="{{ $email }}">
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="otp" value="{{ session('otp_verified') ? '1' : '' }}"> <!-- Menambahkan OTP di form -->

    <!-- Password Baru -->
    <div class="floating-label">
        <div class="input-group">
            <input type="password" id="password" name="password" required minlength="8" placeholder=" " class="floating-input w-full px-4 py-3 pl-12 pr-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
            <label for="password">Password Baru</label>
            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400"></i>
            <button type="button" onclick="togglePassword('password', 'toggleIcon1')" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors">
                <i id="toggleIcon1" class="fas fa-eye"></i>
            </button>
        </div>
        <!-- Password Strength Indicator -->
        <div class="mt-2">
            <div class="password-strength bg-gray-200" id="passwordStrength"></div>
            <p class="text-xs text-gray-500 mt-1" id="passwordStrengthText">Password harus minimal 8 karakter</p>
        </div>
    </div>

    <!-- Konfirmasi Password -->
    <div class="floating-label">
        <div class="input-group">
            <input type="password" id="password_confirmation" name="password_confirmation" required placeholder=" " class="floating-input w-full px-4 py-3 pl-12 pr-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
            <label for="password_confirmation">Konfirmasi Password</label>
            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400"></i>
            <button type="button" onclick="togglePassword('password_confirmation', 'toggleIcon2')" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors">
                <i id="toggleIcon2" class="fas fa-eye"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl hover:from-blue-600 hover:to-blue-800 transition-all duration-300 font-semibold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1">
        <i class="fas fa-shield-alt mr-2"></i>Reset Password
    </button>
</form>

        <!-- Back to Login -->
        <div class="mt-8 text-center">
            <div class="inline-flex items-center px-6 py-3 bg-white rounded-full shadow-md">
                <span class="text-gray-600 mr-2">Sudah ingat password?</span>
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                    Kembali ke Login
                </a>
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
        // Toggle password visibility
        function togglePassword(fieldId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }

        // Check password strength
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('passwordStrengthText');
            
            let strength = 0;
            let feedback = [];
            
            const requirements = {
                length: password.length >= 8,
                upper: /[A-Z]/.test(password),
                lower: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };

            // Update requirement indicators
            for (const [key, met] of Object.entries(requirements)) {
                const element = document.getElementById(`req-${key}`);
                if (element) {
                    const icon = element.querySelector('i');
                    if (met) {
                        icon.className = 'fas fa-check text-green-500 mr-2';
                        strength++;
                    } else {
                        icon.className = 'fas fa-times text-red-500 mr-2';
                        if (key === 'length') feedback.push('minimal 8 karakter');
                        if (key === 'upper') feedback.push('huruf besar');
                        if (key === 'lower') feedback.push('huruf kecil');
                        if (key === 'number') feedback.push('angka');
                        if (key === 'special') feedback.push('karakter khusus');
                    }
                }
            }
            
            // Update strength bar
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
            const widths = ['20%', '40%', '60%', '80%', '100%'];
            const labels = ['Sangat Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];
            
            strengthBar.className = `password-strength ${colors[strength - 1] || 'bg-gray-200'}`;
            strengthBar.style.width = widths[strength - 1] || '0%';
            
            if (strength === 0) {
                strengthText.textContent = 'Password harus minimal 8 karakter';
                strengthText.className = 'text-xs text-gray-500 mt-1';
            } else if (feedback.length > 0) {
                strengthText.textContent = `${labels[strength - 1]} - Tambahkan: ${feedback.join(', ')}`;
                strengthText.className = 'text-xs text-orange-600 mt-1';
            } else {
                strengthText.textContent = labels[strength - 1];
                strengthText.className = 'text-xs text-green-600 mt-1';
            }

            checkPasswordMatch();
            return strength;
        }
        
        // Check password match
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const matchText = document.getElementById('passwordMatchText');
            
            if (confirmPassword === '') {
                matchText.textContent = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchText.textContent = '✓ Password cocok';
                matchText.className = 'text-xs text-green-600 mt-1';
            } else {
                matchText.textContent = '✗ Password tidak cocok';
                matchText.className = 'text-xs text-red-600 mt-1';
            }
        }
        
        // Form submission validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const resetBtn = document.getElementById('resetBtn');
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            
            const strength = checkPasswordStrength();
            
            if (strength < 3) {
                e.preventDefault();
                alert('Password terlalu lemah! Pastikan memenuhi minimal 3 persyaratan.');
                document.getElementById('password').focus();
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok!');
                document.getElementById('password_confirmation').focus();
                return;
            }
            
            // Show loading state
            resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mereset Password...';
            resetBtn.disabled = true;
        });
        
        // Auto-focus on password field
        window.addEventListener('load', function() {
            document.getElementById('password').focus();
        });
    </script>
</body>
</html>