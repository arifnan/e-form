<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\Student;

class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = Auth::user(); 

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $rules = [
            'name' => 'sometimes|string|max:255',
            'password' => 'sometimes|min:6|confirmed',
        ];

        $emailRule = 'sometimes|email|max:255|';
        if ($user instanceof Admin) {
            $emailRule .= Rule::unique('admins', 'email')->ignore($user->id);
        } elseif ($user instanceof Teacher) {
            $emailRule .= Rule::unique('teachers', 'email')->ignore($user->id);
        } elseif ($user instanceof Student) {
            $emailRule .= Rule::unique('students', 'email')->ignore($user->id);
        } else {
            return response()->json(['message' => 'User type not supported for profile update.'], 400);
        }
        $rules['email'] = $emailRule;

        if ($user instanceof Teacher) {
            $rules['nip'] = ['sometimes', 'string', Rule::unique('teachers', 'nip')->ignore($user->id)];
            $rules['subject'] = 'sometimes|string|max:255';
            $rules['address'] = 'sometimes|nullable|string';
            $rules['gender'] = 'sometimes|boolean';
        } elseif ($user instanceof Student) {
            $rules['grade'] = 'sometimes|string|max:255';
            $rules['address'] = 'sometimes|nullable|string';
            $rules['gender'] = 'sometimes|boolean';
        }

        $validatedData = $request->validate($rules);

        if ($request->filled('name')) {
            $user->name = $validatedData['name'];
        }
        if ($request->filled('email')) {
            $user->email = $validatedData['email'];
        }
        if ($request->filled('password')) {
            // Model akan menghash otomatis karena ada 'hashed' cast
            $user->password = $validatedData['password'];
        }

        if ($user instanceof Teacher) {
            if ($request->filled('nip')) $user->nip = $validatedData['nip'];
            if ($request->filled('subject')) $user->subject = $validatedData['subject'];
            if ($request->filled('address')) $user->address = $validatedData['address'];
            if ($request->has('gender')) $user->gender = $validatedData['gender'];
        } elseif ($user instanceof Student) {
            if ($request->filled('grade')) $user->grade = $validatedData['grade'];
            if ($request->filled('address')) $user->address = $validatedData['address'];
            if ($request->has('gender')) $user->gender = $validatedData['gender'];
        }

        $user->save();

        // Sembunyikan password dari response
        $user->makeHidden('password');


        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }
}