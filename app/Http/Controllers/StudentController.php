<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ResponseResource;
use Laravel\Sanctum\HasApiTokens;


// use Illuminate\Support\Facades\View;        // tambahkan ini
// use Illuminate\Support\Facades\Redirect;    // tambahkan ini
// use Illuminate\Support\Facades\Response;    // tambahkan ini
class StudentController extends Controller
{

    public function index(Request $request) {
        $query = Student::query();
    
        // Filter berdasarkan gender
        if ($request->has('gender') && $request->gender !== '') {
            $query->where('gender', $request->gender);
        }
    
        // Filter berdasarkan kelas
        if ($request->has('grade') && $request->grade !== '') {
            $query->where('grade', $request->grade);
        }
    
        $students = $query->get();
    
        return view('students.index', compact('students'));
    }
    

    public function create() {
        return view('students.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required',
            'gender' => 'required|boolean',
            'email' => 'required|email|unique:students',
            'password' => 'required|min:6',
            'grade' => 'required',
            'address' => 'nullable|string',
        ]);
    
        Student::create([
            'name' => $request->name,
            'gender' => $request->gender,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'grade' => $request->grade,
            'address' => $request->address,
        ]);
    
        return redirect()->route('students.index')->with('success', 'Siswa berhasil ditambahkan.');
    }
    
    //menampilkan data api json
    public function apiIndex()
    {
        $query = Student::all();
        return response()->json([
            'status' => true,
            'message' => 'Data murid ditemukan',
            'data' => $query
        ], 200);
    }

     public function apiGetStudentSubmittedResponsesHistory(Request $request)
    {
        $student = Auth::user(); // Asumsi user yang login adalah siswa

        // if (!$student || ($student->role ?? null) !== 'student') {
        //     return response()->json(['message' => 'Unauthorized. Students only.'], 403);
        // }
        // Jika Student model terpisah, Auth::user() akan instance dari Student

        $responsesHistory = $student->submittedResponses() // Menggunakan relasi submittedResponses()
                                     ->with(['form.teacher', 'answers.question.options']) // Eager load form, teacher dari form, dan jawaban beserta pertanyaannya
                                     ->latest('submitted_at') // atau created_at
                                     ->paginate(15); // Paginasi

        return ResponseResource::collection($responsesHistory);
    }
}
