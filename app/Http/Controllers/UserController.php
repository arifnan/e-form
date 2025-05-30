<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\UserResource;
use App\Models\Teacher; // Jika profil guru di tabel teachers
use App\Models\Student; // Jika profil siswa di tabel students
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    
    public function apiUpdateUserProfile(Request $request)
    {
        $user = Auth::user(); // Akan menggunakan guard 'sanctum' karena rute ini di grup middleware 'auth:sanctum'

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Pastikan $user adalah instance dari Teacher atau Student (atau model lain yang valid)
        // Ini penting untuk memastikan $user adalah Eloquent model yang memiliki method save() dan fresh()
        if (!($user instanceof Teacher) && !($user instanceof Student)) {
            // Jika Anda memiliki model User dasar yang juga bisa diupdate via API ini:
            // if (!($user instanceof \App\Models\User) && !($user instanceof Teacher) && !($user instanceof Student)) { ... }

            // Untuk sekarang, kita asumsikan hanya Teacher atau Student yang bisa update profil via endpoint ini.
            Log::error('apiUpdateUserProfile: Authenticated user is not an instance of Teacher or Student. Actual type: ' . get_class($user));
            return response()->json(['message' => 'User type not supported for profile update.'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'nullable|string|max:1000',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Validasi subject hanya jika user adalah Teacher dan field 'subject' dikirim
            'subject' => Rule::requiredIf(fn () => $user instanceof Teacher && $request->has('subject')),
            // Validasi grade hanya jika user adalah Student dan field 'grade' dikirim
            'grade' => Rule::requiredIf(fn () => $user instanceof Student && $request->has('grade')),
        ]);

        // Hanya update field jika ada dalam request
        if ($request->filled('name')) { // 'filled' lebih baik dari 'has' untuk memastikan ada value
            $user->name = $validatedData['name'];
        }
        if ($request->has('address')) { // 'has' oke untuk field nullable seperti address
            $user->address = $validatedData['address'];
        }

        // Update field spesifik peran
        if ($user instanceof Teacher && $request->filled('subject')) {
            $user->subject = $validatedData['subject'];
        }
        if ($user instanceof Student && $request->filled('grade')) {
            $user->grade = $validatedData['grade'];
        }

        if ($request->hasFile('profile_photo')) {
            // Pastikan model Teacher dan Student memiliki atribut 'profile_photo_path'
            // dan kolomnya sudah ada di database (dari migrasi yang Anda buat)
            if (property_exists($user, 'profile_photo_path') && $user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            if (property_exists($user, 'profile_photo_path')) {
                $user->profile_photo_path = $path;
            }
        }

        try {
            $user->save(); // Sekarang seharusnya tidak error jika $user adalah Eloquent Model yang benar
        } catch (\Exception $e) {
            Log::error('Error saving user profile: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to save profile.', 'error' => $e->getMessage()], 500);
        }

        try {
            // Muat ulang instance model dari database untuk mendapatkan data terbaru
            // dan memastikan semua accessor/mutator/cast diterapkan dengan benar.
            $refreshedUser = $user->fresh(); 
        } catch (\Exception $e) {
            Log::error('Error refreshing user model: ' . $e->getMessage());
            // Jika fresh() gagal, kita tetap kembalikan user yang sudah di-save (meskipun mungkin tidak ideal)
            $refreshedUser = $user;
        }
        
        // Muat relasi yang mungkin ingin ditampilkan oleh UserResource
        // $refreshedUser->load('notifications', 'favoriteForms'); // Contoh

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($refreshedUser)
        ]);
    }
}