<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
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
}
