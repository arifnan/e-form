<?php 

namespace App\Http\Controllers;

use App\Models\Response;
use App\Models\ResponseAnswer;
use App\Models\Form;
use App\Models\Response as FormResponseModel; // Alias model Response ke FormResponseModel agar tidak bentrok dengan Illuminate\Http\Response
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ResponseResource; // Gunakan ResponseResource yang sudah dibuat
use Illuminate\Support\Facades\DB; // Untuk transaksi
use Illuminate\Support\Facades\Log; // Untuk logging

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


    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }return response()->json($response, $code);
    }

public function apiSubmitFormResponse(Request $request)
    {
        $student = Auth::user(); // Asumsi user yang login adalah siswa

        // Validasi dasar
        $validatedData = $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer_text' => 'nullable|string', // Bisa jadi null jika jawaban berupa pilihan atau file
            // 'answers.*.option_id' => 'nullable|integer|exists:question_options,id', // Jika mengirim ID opsi
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // Maks 5MB
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $form = Form::findOrFail($validatedData['form_id']);

        // TODO: Tambahkan validasi lokasi jika diperlukan oleh formulir atau pertanyaan tertentu

        DB::beginTransaction();
        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('form_responses/photos', 'public');
            }

            // Buat entri di tabel 'responses'
            $formResponse = $student->submittedResponses()->create([ // Menggunakan relasi submittedResponses
                'form_id' => $form->id,
                'student_id' => $student->id, // Diambil dari user yang terautentikasi
                // 'photo_path' => $photoPath, // photo_path ada di response_answers berdasarkan SQL dump Anda
                // 'latitude' => $validatedData['latitude'] ?? null,
                // 'longitude' => $validatedData['longitude'] ?? null,
                // 'is_location_valid' => true, // Logika validasi lokasi Anda
                // 'submitted_at' akan diisi oleh timestamps
            ]);

            // Simpan setiap jawaban
            foreach ($validatedData['answers'] as $answerData) {
                // Ambil pertanyaan untuk validasi atau info tambahan
                $question = Question::find($answerData['question_id']);
                if (!$question || $question->form_id !== $form->id) {
                    throw new \Exception("Invalid question ID {$answerData['question_id']} for form ID {$form->id}");
                }

                $answer = new Answer([
                    'question_id' => $answerData['question_id'],
                    'answer_text' => $answerData['answer_text'] ?? null,
                    // 'option_id' => $answerData['option_id'] ?? null, // Jika ada
                    // 'file_url' => $photoPath, // Jika foto disimpan per jawaban dan ini adalah pertanyaan foto
                    // 'latitude' => $validatedData['latitude'] ?? null, // Jika lokasi disimpan per jawaban
                    // 'longitude' => $validatedData['longitude'] ?? null,
                ]);
                // Jika foto dan lokasi disimpan di tabel response_answers dan relevan dengan jawaban ini
                if ($question->question_type === 'file_upload' && $photoPath) { // Sesuaikan dengan tipe pertanyaan Anda
                    $answer->file_url = $photoPath;
                }
                if ($question->requires_location && isset($validatedData['latitude']) && isset($validatedData['longitude'])) {
                     $answer->latitude = $validatedData['latitude'];
                     $answer->longitude = $validatedData['longitude'];
                     // $answer->formatted_address = ... // jika Anda mengambil formatted address
                }


                $formResponse->answers()->save($answer);
            }

            DB::commit();
            $formResponse->load(['form', 'student', 'answers.question']); // Eager load untuk resource
            return response()->json(new ResponseResource($formResponse), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($photoPath) { // Hapus foto yang sudah terupload jika terjadi error
                Storage::disk('public')->delete($photoPath);
            }
            Log::error('Failed to submit form response: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to submit response: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menampilkan semua jawaban untuk formulir tertentu (dilihat oleh guru).
     * Sesuai dengan GET /forms/{form}/responses
     */
    public function apiGetResponsesForForm(Request $request, Form $form) // Route Model Binding
    {
        $teacher = Auth::user();

        // Autorisasi: Pastikan guru yang login adalah pemilik formulir
        if ($form->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'Unauthorized to view responses for this form.'], 403);
        }

        $responses = FormResponseModel::where('form_id', $form->id)
                                    ->with(['student', 'answers.question.options']) // Eager load detail siswa dan jawaban beserta pertanyaannya
                                    ->latest('submitted_at') // atau created_at
                                    ->paginate(15);

        return ResponseResource::collection($responses);
    }

}