<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sistem Analisis Sentimen</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        .register-card {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 20px 60px -15px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        .input-wrapper {
            position: relative;
        }
        .input-label {
            position: absolute;
            left: 3rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.875rem;
            transition: opacity 0.2s ease;
            pointer-events: none;
        }
        .input-wrapper.textarea-wrapper .input-label {
            top: 1.25rem;
            transform: none;
        }
        .input-wrapper input:focus + .input-label,
        .input-wrapper input:not(:placeholder-shown) + .input-label,
        .input-wrapper textarea:focus + .input-label,
        .input-wrapper textarea:not(:placeholder-shown) + .input-label,
        .input-wrapper select:focus + .input-label,
        .input-wrapper.has-value .input-label {
            opacity: 0;
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
<body class="gradient-bg min-h-screen flex items-center justify-center p-4 py-12">
    <div class="register-card rounded-2xl p-10 w-full max-w-3xl"
        <!-- Header Section -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-700 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                <i class="fas fa-user-plus text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent mb-3">
                Daftar Akun Baru
            </h1>
            <p class="text-gray-600 text-lg">Bergabunglah dengan Sistem Analisis Sentimen</p>
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
        
        <!-- Register Form -->
        <form method="POST" action="{{ route('register') }}" class="space-y-6" id="registerForm">
            @csrf
            
            <!-- Personal Information Section -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-2xl">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user mr-2 text-blue-600"></i>
                    Informasi Pribadi
                </h3>
                
                <div class="form-grid">
                    <!-- Nama Lengkap -->
                    <div class="input-wrapper">
                        <div class="input-group">
                            <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400 z-10"></i>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   required 
                                   placeholder=" "
                                   class="w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
                            <span class="input-label">Nama Lengkap</span>
                        </div>
                    </div>

                    <!-- NIP -->
                    <div class="input-wrapper">
                        <div class="input-group">
                            <i class="fas fa-id-card absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400 z-10"></i>
                            <input type="text"
                                   id="nip"
                                   name="nip"
                                   value="{{ old('nip') }}"
                                   placeholder=" "
                                   class="w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
                            <span class="input-label">NIP</span>
                        </div>
                    </div>

                    <!-- Jabatan -->
                    <div class="input-wrapper">
                        <div class="input-group">
                            <i class="fas fa-briefcase absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400 z-10"></i>
                            <input type="text"
                                   id="jabatan"
                                   name="jabatan"
                                   value="{{ old('jabatan') }}"
                                   placeholder=" "
                                   class="w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
                            <span class="input-label">Jabatan</span>
                        </div>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="input-wrapper has-value">
                        <div class="input-group">
                            <i class="fas fa-venus-mars absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400 z-10"></i>
                            <select id="jenis_kelamin"
                                    name="jenis_kelamin"
                                    class="w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
                                <option value="" {{ old('jenis_kelamin')==='' ? 'selected' : '' }}>Pilih Jenis Kelamin</option>
                                <option value="L" {{ old('jenis_kelamin')==='L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin')==='P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tanggal Lahir -->
                    <div class="input-wrapper" id="tanggalLahirWrapper">
                        <div class="input-group">
                            <i class="fas fa-calendar-day absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400 z-10"></i>
                            <input type="date"
                                   id="tanggal_lahir"
                                   name="tanggal_lahir"
                                   value="{{ old('tanggal_lahir') }}"
                                   placeholder=" "
                                   class="w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
                            <span class="input-label">Tanggal Lahir</span>
                        </div>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="mt-6">
                    <div class="input-wrapper textarea-wrapper">
                        <div class="input-group">
                            <i class="fas fa-map-marker-alt absolute left-4 top-6 text-blue-400 z-10"></i>
                            <textarea id="alamat"
                                      name="alamat"
                                      rows="3"
                                      placeholder=" "
                                      class="w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white resize-none">{{ old('alamat') }}</textarea>
                            <span class="input-label">Alamat</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Information Section -->
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-6 rounded-2xl">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-key mr-2 text-blue-600"></i>
                    Informasi Akun
                </h3>
                
                <!-- Email -->
                <div class="input-wrapper mb-6">
                    <div class="input-group">
                        <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400 z-10"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               required 
                               placeholder=" "
                               class="w-full px-4 py-3 pl-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
                        <span class="input-label">Alamat Email</span>
                    </div>
                </div>
                
                <div class="form-grid">
                    <!-- Password -->
                    <div class="input-wrapper">
                        <div class="input-group">
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400 z-10"></i>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="8"
                                   placeholder=" "
                                   class="w-full px-4 py-3 pl-12 pr-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white"
                                   onkeyup="checkPasswordStrength()">
                            <span class="input-label">Password</span>
                            <button type="button" 
                                    onclick="togglePassword('password')" 
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors z-10">
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
                    <div class="input-wrapper">
                        <div class="input-group">
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400 z-10"></i>
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required 
                                   placeholder=" "
                                   class="w-full px-4 py-3 pl-12 pr-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white"
                                   onkeyup="checkPasswordMatch()">
                            <span class="input-label">Konfirmasi Password</span>
                            <button type="button" 
                                    onclick="togglePassword('password_confirmation')" 
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-600 transition-colors z-10">
                                <i id="toggleIcon2" class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-xs mt-1" id="passwordMatchText"></p>
                    </div>
                </div>
            </div>
            
            <!-- Terms and Submit -->
            <div class="space-y-6">
                <!-- Terms Checkbox -->
                <div class="flex items-start p-4 bg-gray-50 rounded-xl">
                    <input type="checkbox" 
                           id="terms" 
                           name="terms" 
                           required
                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                    <label for="terms" class="ml-3 text-sm text-gray-600">
                        Saya setuju dengan <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">syarat dan ketentuan</a> 
                        serta <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">kebijakan privasi</a>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl hover:from-blue-600 hover:to-blue-800 transition-all duration-300 font-semibold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                        id="submitBtn">
                    <i class="fas fa-rocket mr-2"></i>Daftar Sekarang
                </button>
            </div>
        </form>
        
        <!-- Login Link -->
        <div class="mt-8 text-center">
            <div class="inline-flex items-center px-6 py-3 bg-white rounded-full shadow-md">
                <span class="text-gray-600 mr-2">Sudah punya akun?</span>
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                    Masuk di sini
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
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(fieldId === 'password' ? 'toggleIcon1' : 'toggleIcon2');
            
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
            
            if (password.length >= 8) strength++;
            else feedback.push('minimal 8 karakter');
            
            if (/[a-z]/.test(password)) strength++;
            else feedback.push('huruf kecil');
            
            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('huruf besar');
            
            if (/[0-9]/.test(password)) strength++;
            else feedback.push('angka');
            
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            else feedback.push('karakter khusus');
            
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
        
        // Handle select change to hide label
        document.getElementById('jenis_kelamin').addEventListener('change', function() {
            if (this.value !== '') {
                document.getElementById('jenisKelaminWrapper').classList.add('has-value');
            } else {
                document.getElementById('jenisKelaminWrapper').classList.remove('has-value');
            }
        });
        
        // Handle date input to hide label
        document.getElementById('tanggal_lahir').addEventListener('change', function() {
            if (this.value !== '') {
                document.getElementById('tanggalLahirWrapper').classList.add('has-value');
            } else {
                document.getElementById('tanggalLahirWrapper').classList.remove('has-value');
            }
        });
        
        // Check on page load for old values
        window.addEventListener('load', function() {
            if (document.getElementById('jenis_kelamin').value !== '') {
                document.getElementById('jenisKelaminWrapper').classList.add('has-value');
            }
            if (document.getElementById('tanggal_lahir').value !== '') {
                document.getElementById('tanggalLahirWrapper').classList.add('has-value');
            }
        });
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const terms = document.getElementById('terms').checked;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('Anda harus menyetujui syarat dan ketentuan!');
                return;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mendaftarkan...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>