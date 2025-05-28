<?php 

namespace App\Http\Controllers;

use App\Models\Response;
use App\Models\ResponseAnswer;
use Illuminate\Http\Request; // Tambahkan ini
use Illuminate\Support\Facades\Auth; // Tambahkan ini jika belum ada

class ResponseController extends Controller
{
    public function index(Request $request) // Tambahkan Request $request jika ada filter
    {
        $query = Response::with(['form', 'answers.question']); // Load relasi

        // Filter berdasarkan form_id jika ada di request
        if ($request->has('form_id') && $request->form_id != '') {
            $query->where('form_id', $request->form_id);
        }

        $responses = $query->get();
        return view('responses.index', compact('responses'));
    }

    // Method untuk menampilkan detail dari sebuah response
    public function show(Response $response)
    {
        $response->load(['form', 'answers.question', 'answers.option']); // Load relasi yang dibutuhkan
        return view('responses.show', compact('response'));
    }


    public function destroy(Response $response)
    {
        // Hapus semua jawaban terkait terlebih dahulu
        $response->answers()->delete();
        // Kemudian hapus response itu sendiri
        $response->delete();
        return redirect()->route('responses.index')->with('success', 'Response deleted.');
    }

    // API
    public function apiIndex(Request $request) // Tambahkan Request $request
    {
        $query = Response::with(['form', 'answers.question', 'student:id,name']); // Sertakan student

        if ($request->has('form_id') && $request->form_id != '') {
            $query->where('form_id', $request->form_id);
        }
        
        return response()->json($query->get());
    }

    public function apiStore(Request $request)
    {
        $validatedData = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.option_id' => 'nullable|exists:question_options,id',
            'answers.*.file_url' => 'nullable|string|max:2048', // Batasi ukuran file jika perlu
            'answers.*.latitude' => 'nullable|numeric',
            'answers.*.longitude' => 'nullable|numeric',
            'answers.*.formatted_address' => 'nullable|string',
            // student_id tidak perlu divalidasi di sini jika diambil dari Auth::user()
        ]);

        $studentId = null;
        if (Auth::check() && Auth::user() instanceof \App\Models\Student) {
            $studentId = Auth::id();
        } elseif ($request->has('student_id')) { // Fallback jika student_id dikirim manual (misal oleh admin)
             $studentId = $request->student_id;
             // Anda mungkin ingin menambahkan validasi exists:students,id di sini jika student_id dikirim manual
        }


        $response = Response::create([
            'form_id' => $validatedData['form_id'],
            'student_id' => $studentId, // Simpan student_id
        ]);

        foreach ($validatedData['answers'] as $answerData) {
            ResponseAnswer::create([
                'response_id' => $response->id,
                'question_id' => $answerData['question_id'],
                'answer_text' => $answerData['answer_text'] ?? null,
                'option_id' => $answerData['option_id'] ?? null,
                'file_url' => $answerData['file_url'] ?? null,
                'latitude' => $answerData['latitude'] ?? null,
                'longitude' => $answerData['longitude'] ?? null,
                'formatted_address' => $answerData['formatted_address'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Jawaban berhasil disimpan', 'response' => $response->load('answers')], 201);
    }

    public function apiDestroy(Response $response)
    {
        // Hapus semua jawaban terkait terlebih dahulu
        $response->answers()->delete();
        // Kemudian hapus response itu sendiri
        $response->delete();
        return response()->json(['message' => 'Response deleted']);
    }
}