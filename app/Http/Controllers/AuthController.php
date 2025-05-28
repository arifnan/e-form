<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ResponseController // Pastikan extend ResponseController
{
    public function registerGuru(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:20|unique:users,nip',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru',
            'address' => $request->address,
        ]);

        $token = $user->createToken('auth_token_guru_'. $user->id)->plainTextToken;
        $user->profile_photo_url = $user->profile_photo_path ? Storage::url($user->profile_photo_path) : null;


        return $this->sendResponse(['token' => $token, 'user' => $user], 'Registrasi Guru berhasil.');
    }

    public function loginGuru(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all(), 422);
        }

        $user = User::where('nip', $request->nip)->first();

        if (!$user || !Hash::check($request->password, $user->password) || $user->role !== 'guru') {
            return $this->sendError('NIP atau Password salah, atau peran bukan guru.', [], 401);
        }

        $token = $user->createToken('auth_token_guru_'. $user->id)->plainTextToken;
        $user->profile_photo_url = $user->profile_photo_path ? Storage::url($user->profile_photo_path) : null;

        return $this->sendResponse(['token' => $token, 'user' => $user], 'Login Guru berhasil.');
    }

    public function registerSiswa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
            'nip' => null,
            'address' => $request->address,
        ]);

        $token = $user->createToken('auth_token_siswa_'. $user->id)->plainTextToken;
        $user->profile_photo_url = $user->profile_photo_path ? Storage::url($user->profile_photo_path) : null;

        return $this->sendResponse(['token' => $token, 'user' => $user], 'Registrasi Siswa berhasil.');
    }

    public function loginSiswa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password) || $user->role !== 'siswa') {
            return $this->sendError('Email atau Password salah, atau peran bukan siswa.', [], 401);
        }

        $token = $user->createToken('auth_token_siswa_'. $user->id)->plainTextToken;
        $user->profile_photo_url = $user->profile_photo_path ? Storage::url($user->profile_photo_path) : null;

        return $this->sendResponse(['token' => $token, 'user' => $user], 'Login Siswa berhasil.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse([], 'Logout berhasil.');
    }
}