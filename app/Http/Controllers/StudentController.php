<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // Tambahkan ini
use App\Models\Response;
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


public function apiGetResponseHistory(Request $request)
    {
        $student = Auth::user(); // Mengambil siswa yang terautentikasi

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthenticated or not a student.'], 401);
        }

        $responses = Response::where('student_id', $student->id)
                             ->with(['form' => function ($query) {
                                 $query->select('id', 'title', 'teacher_id')->with('teacher:id,name'); // Memuat form dengan judul dan nama guru pembuat
                             }, 'answers']) // Memuat jawaban terkait
                             ->orderBy('created_at', 'desc')
                             ->get();
        
        // Transformasi data untuk menyertakan detail yang lebih baik
        $history = $responses->map(function ($response) {
            return [
                'response_id' => $response->id,
                'form_id' => $response->form->id,
                'form_title' => $response->form->title,
                'form_creator' => $response->form->teacher->name ?? 'N/A',
                'submitted_at' => $response->created_at->toDateTimeString(),
                'total_answers' => $response->answers->count(),
                // Anda bisa menambahkan detail jawaban jika diperlukan
            ];
        });


        return response()->json($history);
    }

}
