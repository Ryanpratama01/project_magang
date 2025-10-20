<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get profile data user yang sedang login
     */
    public function getProfile()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }
            
            // Format data user
            $userData = [
                'id_user' => $user->id_user,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'nip' => $user->nip,
                'jabatan' => $user->jabatan,
                'jenis_kelamin' => $user->jenis_kelamin,
                'tanggal_lahir' => $user->tanggal_lahir,
                'alamat' => $user->alamat,
                'role' => $user->role,
                'created_at' => $user->created_at ? $user->created_at->format('d-m-Y H:i:s') : null,
                'updated_at' => $user->updated_at ? $user->updated_at->format('d-m-Y H:i:s') : null,
            ];
            
            return response()->json([
                'success' => true,
                'user' => $userData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update profile user
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Validasi input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . $user->id_user . ',id_user',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id_user . ',id_user',
                'nip' => 'nullable|string|max:50',
                'jabatan' => 'nullable|string|max:100',
                'jenis_kelamin' => 'nullable|in:L,P',
                'tanggal_lahir' => 'nullable|date',
                'alamat' => 'nullable|string|max:500',
                'password' => 'nullable|min:6|confirmed', // password_confirmation
            ], [
                'name.required' => 'Nama harus diisi',
                'username.required' => 'Username harus diisi',
                'username.unique' => 'Username sudah digunakan',
                'email.required' => 'Email harus diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan',
                'jenis_kelamin.in' => 'Jenis kelamin harus L atau P',
                'tanggal_lahir.date' => 'Format tanggal tidak valid',
                'password.min' => 'Password minimal 6 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update data user
            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;
            $user->nip = $request->nip;
            $user->jabatan = $request->jabatan;
            $user->jenis_kelamin = $request->jenis_kelamin;
            $user->tanggal_lahir = $request->tanggal_lahir;
            $user->alamat = $request->alamat;

            // Update password jika diisi
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diupdate',
                'user' => [
                    'id_user' => $user->id_user,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'nip' => $user->nip,
                    'jabatan' => $user->jabatan,
                    'jenis_kelamin' => $user->jenis_kelamin,
                    'tanggal_lahir' => $user->tanggal_lahir,
                    'alamat' => $user->alamat,
                    'role' => $user->role,
                    'updated_at' => $user->updated_at->format('d-m-Y H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}