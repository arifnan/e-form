<?php

namespace App\Http\Controllers\Api;// <-- Namespace disesuaikan

use App\Http\Controllers\Controller; // <-- Extends Controller dasar Laravel
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // <-- Ini bisa digunakan untuk $request->user() setelah middleware Sanctum
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource; // Pastikan UserResource ada dan sesuai

class AuthController extends Controller // <-- Nama kelasnya AuthController
{
    /**
     * Registrasi untuk Guru via API.
     */
    public function registerTeacher(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:teachers,nip',
            'email' => 'required|string|email|max:255|unique:teachers,email',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'required|boolean',
            'subject' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        $teacher = Teacher::create([
            'name' => $validatedData['name'],
            'nip' => $validatedData['nip'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'gender' => $validatedData['gender'],
            'subject' => $validatedData['subject'],
            'address' => $validatedData['address'] ?? null,
        ]);

        $token = $teacher->createToken('api-token-teacher-' . $teacher->id)->plainTextToken;

        return response()->json([
            'message' => 'Teacher registered successfully',
            // Asumsi UserResource bisa menghandle instance Teacher
            'user' => new UserResource($teacher->fresh()->load('notifications', 'favoriteForms')),
            'token' => $token,
            'role' => 'teacher'
        ], 201);
    }

    /**
     * Registrasi untuk Siswa via API.
     */
    public function registerStudent(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students,email',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'required|boolean',
            'grade' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        $student = Student::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'gender' => $validatedData['gender'],
            'grade' => $validatedData['grade'],
            'address' => $validatedData['address'] ?? null,
        ]);

        $token = $student->createToken('api-token-student-' . $student->id)->plainTextToken;

        return response()->json([
            'message' => 'Student registered successfully',
            // Asumsi UserResource bisa menghandle instance Student
            'user' => new UserResource($student->fresh()->load('notifications', 'favoriteForms')),
            'token' => $token,
            'role' => 'student'
        ], 201);
    }

    /**
     * Login untuk Guru atau Siswa via API.
     */
    public function loginUser(Request $request)
    {
        $request->validate([
            'email' => 'required|string', // Bisa NIP (untuk guru) atau Email (untuk guru/siswa)
            'password' => 'required|string',
        ]);

        $loginInput = $request->input('email');

        // Coba autentikasi sebagai Teacher
        $teacher = Teacher::where('nip', $loginInput)->orWhere('email', $loginInput)->first();
        if ($teacher && Hash::check($request->password, $teacher->password)) {
            $token = $teacher->createToken('api-token-teacher-' . $teacher->id)->plainTextToken;
            return response()->json([
                'message' => 'Login successful as teacher',
                'user' => new UserResource($teacher->load('notifications', 'favoriteForms')),
                'token' => $token,
                'role' => 'teacher'
            ]);
        }

        // Coba autentikasi sebagai Student
        $student = Student::where('email', $loginInput)->first();
        if ($student && Hash::check($request->password, $student->password)) {
            $token = $student->createToken('api-token-student-' . $student->id)->plainTextToken;
            return response()->json([
                'message' => 'Login successful as student',
                'user' => new UserResource($student->load('notifications', 'favoriteForms')),
                'token' => $token,
                'role' => 'student'
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
        ]);
    }

    /**
     * Logout untuk pengguna API (menghapus token Sanctum).
     */
    public function logoutUser(Request $request)
{
    $user = $request->user();
    if ($user && method_exists($user, 'currentAccessToken')) {
        $token = $user->currentAccessToken();
        if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
            $token->delete();
            return response()->json(['message' => 'Logged out successfully from API']);
        }
    }
    return response()->json(['message' => 'Logout failed or no active API token.'], 400);
}

//VERSI EROR 
// public function logoutUser(Request $request)
//     {
//         $user = $request->user(); // Pengguna yang diautentikasi oleh Sanctum

//         if ($user && method_exists($user, 'currentAccessToken')) {
//             $token = $user->currentAccessToken();
//             if ($token) {
//                 $token->delete(); // Menghapus token Sanctum yang sedang digunakan
//                 return response()->json(['message' => 'Logged out successfully from API']);
//             }
//         }
//         return response()->json(['message' => 'Logout failed or no active API token.'], 400);
//     }

    /**
     * Mendapatkan detail pengguna API yang terautentikasi.
     */
    public function getAuthenticatedUser(Request $request)
    {
        $user = $request->user(); // Pengguna yang diautentikasi oleh Sanctum

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        // UserResource akan menangani penampilan role berdasarkan instance model
        return response()->json(new UserResource($user->load('notifications', 'favoriteForms')));
    }
}