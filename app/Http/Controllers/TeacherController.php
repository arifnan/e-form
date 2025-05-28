<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\Form;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\View;        // tambahkan ini
// use Illuminate\Support\Facades\Redirect;    // tambahkan ini
// use Illuminate\Support\Facades\Response;    // tambahkan ini
class TeacherController extends Controller
{
    public function index(Request $request) {
        $query = Teacher::query();
    
        // Filter berdasarkan gender
        if ($request->has('gender') && $request->gender !== '') {
            $query->where('gender', $request->gender);
        }
    
        // Filter berdasarkan mata pelajaran
        if ($request->has('subject') && $request->subject !== '') {
            $query->where('subject', 'like', '%' . $request->subject . '%');
        }
    
        $teachers = $query->get();
    
        return view('teachers.index', compact('teachers'));
    }
    

    public function create() {
        return view('teachers.create');
    }

    public function store(Request $request) {
    $request->validate([
        'nip' => 'required|unique:teachers,nip',
        'name' => 'required',
        'gender' => 'required|boolean',
        'email' => 'required|email|unique:teachers',
        'password' => 'required|min:6',
        'subject' => 'required',
        'address' => 'nullable|string',
    ]);

    Teacher::create([
        'nip' => $request->nip,
        'name' => $request->name,
        'gender' => $request->gender,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'subject' => $request->subject,
        'address' => $request->address,
    ]);

    return redirect()->route('teachers.index')->with('success', 'Guru berhasil ditambahkan.');
}

    //menampilkan data api json
    public function apiIndex()
    {
        $query = Teacher::all();
        return response()->json([
            'status' => true,
            'message' => 'Data guru ditemukan',
            'data' => $query
        ], 200);
    }


 public function apiRegister(Request $request)
    {
        $validatedData = $request->validate([
            'nip' => 'required|string|unique:teachers,nip',
            'name' => 'required|string|max:255',
            'gender' => 'required|boolean',
            'email' => 'required|string|email|max:255|unique:teachers,email',
            'password' => 'required|string|min:6|confirmed',
            'subject' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        $teacher = Teacher::create([
            'nip' => $validatedData['nip'],
            'name' => $validatedData['name'],
            'gender' => $validatedData['gender'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'], // Model akan otomatis hash
            'subject' => $validatedData['subject'],
            'address' => $validatedData['address'] ?? null,
        ]);

        // Buat token untuk guru yang baru diregistrasi
        $token = $teacher->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Teacher registered successfully',
            'teacher' => $teacher,
            'token' => $token
        ], 201);
    }



public function apiGetFormHistory(Request $request)
    {
        $teacher = Auth::user(); // Mengambil guru yang terautentikasi

        if (!$teacher || !($teacher instanceof \App\Models\Teacher)) {
             return response()->json(['message' => 'Unauthenticated or not a teacher.'], 401);
        }

        $forms = Form::where('teacher_id', $teacher->id)
                     ->withCount('responses') // Menghitung jumlah respons untuk setiap form
                     ->orderBy('created_at', 'desc')
                     ->get();

        return response()->json($forms);
    }

}
