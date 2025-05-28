<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Tetap import Hash untuk metode apiAdminLogin jika diperlukan di sana
use App\Models\Admin;
use Illuminate\Validation\ValidationException; // Untuk error response yang lebih baik di API

class AuthController extends Controller
{
    public function showRegister() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            // Mengubah validasi unique untuk merujuk ke tabel 'admins' dan kolom 'email'
            'email' => 'required|string|email|max:255|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            // Model Admin akan otomatis melakukan hashing karena ada 'hashed' cast pada model
            'password' => $request->password,
            // 'role' => 'admin' // Dihapus karena kolom 'role' tidak ada di tabel 'admins'
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function showLogin() {
        return view('auth.login');
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6', // Anda mungkin ingin menghapus min:6 di sini, biarkan proses attempt yang memvalidasi
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            // 'password.min' => 'Password minimal 6 karakter.', // Bisa dihapus jika tidak relevan saat login
        ]);
    
        // Menggunakan guard 'admin' secara eksplisit untuk login web admin
        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard'); // Arahkan ke dashboard default setelah login
        }
    
        return back()->with('error', 'Email atau password salah.')->withInput();
    }
    
    public function logout(Request $request) { // Tambahkan Request $request untuk manajemen sesi
        // Menggunakan guard 'admin' secara eksplisit untuk logout web admin
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // --- Metode untuk API Login Admin (jika Anda sudah membuatnya atau akan membuatnya) ---
    public function apiAdminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            // Anda bisa menggunakan ValidationException untuk respons error yang lebih standar
            // throw ValidationException::withMessages([
            //     'email' => [trans('auth.failed')],
            // ]);
            return response()->json(['message' => 'Kredensial tidak valid'], 401);
        }

        // 'role:admin' adalah contoh ability/scope token, sesuaikan jika perlu
        $token = $admin->createToken('admin-api-token', ['role:admin'])->plainTextToken;

        return response()->json([
            'message' => 'Admin login successful',
            'admin' => $admin->makeHidden('password'), // Sembunyikan password dari respons
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}