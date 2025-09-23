<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Mail\ResetPasswordOTP;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'NIP' => $validated['nip'] ?? null,
            'jabatan' => $validated['jabatan'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'role' => 'user', // default role
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan masuk.');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }

    public function showForgetPasswordForm()
    {
        return view('auth.forget-password');
    }

    /**
     * Kirim email reset password
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Cek apakah email ada di database
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'Email tidak ditemukan dalam sistem kami.'
            ]);
        }

        // Generate OTP (6 digit)
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Simpan OTP di session untuk debugging (hapus di production)
        session(['debug_otp' => $otp, 'reset_email' => $request->email]);
        
        // Hapus token lama jika ada
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        
        // Simpan OTP ke database
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($otp), // Hash OTP untuk keamanan
            'created_at' => now(),
        ]);

        // Simulasi pengiriman email (untuk development)
        // Dalam produksi, Anda perlu konfigurasi mail driver yang sebenarnya
        try {
            Mail::to($request->email)->send(new ResetPasswordOTP($otp));
            return redirect()->route('password.verify.otp')->with('status', 'Kode OTP telah dikirim ke email Anda. Silakan cek inbox atau folder spam.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Gagal mengirim email. Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Tampilkan form reset password dengan OTP
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token, 
            'email' => $request->email
        ]);
    }

    /**
     * Reset password menggunakan OTP
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            // 'otp' => 'required|string|size:6',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Cari token di database
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenData) {
            return back()->withErrors(['email' => 'Token reset password tidak ditemukan.']);
        }

        // Verifikasi OTP dari session
        if (session('otp_verified') != '1') {
            return back()->withErrors(['otp' => 'OTP tidak valid atau sudah kadaluarsa.']);
        }

        // Proses reset password jika OTP valid
        $user = User::where('email', $request->email)->first();
        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->save();

        // Hapus token OTP
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        session()->forget('otp_verified');  // Menghapus OTP yang sudah diverifikasi

        return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
    }

    /**
     * Tampilkan form verifikasi OTP
     */
    public function showVerifyOtpForm()
    {
        // Cek apakah ada email di session
        if (!session('reset_email')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Silakan masukkan email terlebih dahulu.']);
        }

        return view('auth.verify-otp');
    }

    /**
     * Verifikasi OTP dan tampilkan form reset password
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $email = session('reset_email');
        
        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Session expired. Silakan mulai ulang.']);
        }

        // Cari token di database
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$tokenData) {
            return back()->withErrors(['otp' => 'Kode OTP tidak ditemukan atau sudah expired.']);
        }

        // Cek apakah token sudah expired (15 menit)
        if (now()->diffInMinutes($tokenData->created_at) > 10) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            session()->forget(['debug_otp', 'reset_email']);
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Kode OTP sudah expired. Silakan request ulang.']);
        }

        // Verifikasi OTP
        if (!Hash::check($request->otp, $tokenData->token)) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid.']);
        }

        // OTP valid, simpan di session untuk form reset password
        session(['otp_verified' => true]);

        return redirect()->route('password.reset', ['token' => 'verified', 'email' => $email])
            ->with('success', 'Kode OTP berhasil diverifikasi. Silakan masukkan password baru.');
    }

    /**
     * Resend OTP
     */
    public function resendOtp()
    {
        $email = session('reset_email');
        
        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Session expired. Silakan mulai ulang.']);
        }

        // Generate OTP baru
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update token di database
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->update([
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]);

        // Update debug session
        session(['debug_otp' => $otp]);

        return back()->with('status', 
            'Kode OTP baru telah dikirim ke email Anda. ' . 
            '(Debug: OTP = ' . $otp . ')'  // Hapus di production
        );
    }
}
