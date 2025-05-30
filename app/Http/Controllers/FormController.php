<?
namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Teacher;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Resources\FormResource;
use App\Http\Resources\QuestionResource; // Jika diperlukan untuk validasi atau detail
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Untuk transaksi database

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::with('teacher')->get();
        return view('forms.index', compact('forms'));
    }

    public function create()
    {
        $teachers = Teacher::all();
        return view('forms.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'teacher_id' => 'required|exists:teachers,id'
        ]);

        Form::create($request->all());
        return redirect()->route('forms.index')->with('success', 'Form created.');
    }

    public function edit(Form $form)
    {
        $teachers = Teacher::all();
        return view('forms.edit', compact('form', 'teachers'));
    }

    public function update(Request $request, Form $form)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'teacher_id' => 'required|exists:teachers,id'
        ]);

        $form->update($request->all());
        return redirect()->route('forms.index')->with('success', 'Form updated.');
    }

    public function destroy(Form $form)
    {
        $form->delete();
        return redirect()->route('forms.index')->with('success', 'Form deleted.');
    }

    // API methods
    public function apiIndex()
    {
        return response()->json(Form::with('teacher')->get());
    }

    public function apiStore(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'teacher_id' => 'required|exists:teachers,id'
        ]);

        $form = Form::create($data);
        return response()->json($form, 201);
    }

    public function apiUpdate(Request $request, Form $form)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'teacher_id' => 'required|exists:teachers,id'
        ]);

        $form->update($data);
        return response()->json($form);
    }

    public function apiDestroy(Form $form)
    {
        $form->delete();
        return response()->json(['message' => 'Form deleted']);
    }

public function apiGetTeacherForms(Request $request)
    {
        $teacher = Auth::user(); // Asumsi user yang login adalah guru

        // Pastikan user adalah guru (jika Anda memiliki model User generik dengan role)
        // if (!$teacher || ($teacher->role ?? null) !== 'teacher') {
        //     return response()->json(['message' => 'Unauthorized. Only teachers can access this resource.'], 403);
        // }
        // Jika Teacher adalah model terpisah, Auth::user() akan mengembalikan instance Teacher

        $forms = $teacher->createdForms() // Menggunakan relasi createdForms() di model Teacher/User
                         ->with(['questions.options', 'responses']) // Eager load questions, options, dan responses
                         ->latest()
                         ->paginate(15);

        return FormResource::collection($forms);
    }

    /**
     * Menyimpan formulir baru yang dibuat oleh guru.
     * Sesuai dengan POST /forms di routes/api.php
     */
    public function apiCreateForm(Request $request)
    {
        $teacher = Auth::user();

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => ['required', Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale', 'file_upload', 'short_text', 'long_text', 'dropdown', 'true_false'])], // Sesuaikan dengan enum di DB dan Android
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*' => 'nullable|string|max:255', // Validasi untuk setiap opsi
            'questions.*.required' => 'required|boolean',
            // Validasi untuk LinearScale (jika opsi disimpan terpisah)
            // 'questions.*.min_value' => 'nullable|integer',
            // 'questions.*.max_value' => 'nullable|integer|gt:questions.*.min_value',
            // 'questions.*.min_label' => 'nullable|string|max:255',
            // 'questions.*.max_label' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $form = $teacher->createdForms()->create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'form_code' => Str::upper(Str::random(8)), // Generate kode unik
            ]);

            foreach ($validatedData['questions'] as $questionData) {
                $question = $form->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'question_type' => $this->mapMobileQuestionTypeToDb($questionData['question_type']), // Mapping tipe pertanyaan
                    'is_required' => $questionData['required'],
                    // 'requires_location' => $questionData['requires_location'] ?? false, // Jika ada field ini
                ]);

                // Simpan options jika ada dan relevan dengan tipe pertanyaan
                if (in_array($question->question_type, ['multiple_choice', 'checkbox', 'dropdown']) && !empty($questionData['options'])) {
                    foreach ($questionData['options'] as $optionText) {
                        if (!empty($optionText)) { // Hanya simpan opsi yang tidak kosong
                            $question->options()->create(['option_text' => $optionText]);
                        }
                    }
                }
                // Penanganan untuk LinearScale jika disimpan di question_options
                // (Struktur tabel 'questions' Anda tidak secara langsung menyimpan min/max/label, jadi ini mungkin melalui 'options')
                // Atau jika LinearScale dari mobile disimpan sebagai JSON di kolom 'options' pada tabel 'questions' (perlu parsing)
                if ($questionData['question_type'] === 'LinearScale' && isset($questionData['options']) && count($questionData['options']) >= 2) {
                    // Opsi 0: min_value, 1: max_value, 2: min_label, 3: max_label (sesuai CreateFormViewModel)
                    // Simpan ini sebagai QuestionOption atau dalam format JSON di kolom Question yang didukung
                    // Contoh sederhana jika disimpan sebagai beberapa QuestionOption:
                     if(isset($questionData['options'][0])) $question->options()->create(['option_text' => "min_value:".$questionData['options'][0]]);
                     if(isset($questionData['options'][1])) $question->options()->create(['option_text' => "max_value:".$questionData['options'][1]]);
                     if(isset($questionData['options'][2]) && !empty($questionData['options'][2])) $question->options()->create(['option_text' => "min_label:".$questionData['options'][2]]);
                     if(isset($questionData['options'][3]) && !empty($questionData['options'][3])) $question->options()->create(['option_text' => "max_label:".$questionData['options'][3]]);
                }
            }

            DB::commit();
            $form->load(['teacher', 'questions.options']);
            return response()->json(new FormResource($form), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create form: ' . $e->getMessage()], 500);
        }
    }

    // Helper untuk mapping tipe pertanyaan dari mobile ke format DB
    private function mapMobileQuestionTypeToDb(string $mobileType): string
    {
        return match (strtolower($mobileType)) {
            'text', 'shorttext', 'longtext' => 'short_text', // atau 'long_text' sesuai kebutuhan
            'multiplechoice' => 'multiple_choice',
            'checkbox' => 'checkbox',
            'linearscale' => 'dropdown', // Atau tipe lain yang Anda gunakan untuk skala di DB
            // Tambahkan mapping lain jika perlu
            default => 'short_text', // Default jika tidak dikenal
        };
    }


    /**
     * Menampilkan detail formulir spesifik.
     * Sesuai dengan GET /forms/{form}
     */
    public function apiGetFormDetails(Request $request, Form $form) // Route Model Binding
    {
        // Autorisasi: cek apakah pengguna (guru) adalah pemilik form, atau apakah siswa boleh melihatnya
        // Untuk saat ini, kita asumsikan form bisa dilihat jika ditemukan
        $form->load(['teacher', 'questions.options', 'responses']); // Eager load
        return new FormResource($form);
    }

    /**
     * Menampilkan formulir berdasarkan kode uniknya.
     * Sesuai dengan GET /forms/code/{form_code}
     */
    public function apiGetByFormCode(Request $request, $form_code)
    {
        $form = Form::where('form_code', strtoupper($form_code))
                    ->with(['teacher', 'questions.options']) // Eager load
                    ->first();

        if (!$form) {
            return response()->json(['message' => 'Form not found.'], 404);
        }
        return new FormResource($form);
    }


    /**
     * Memperbarui formulir yang sudah ada.
     * Sesuai dengan PUT /forms/{form}
     */
    public function apiUpdateForm(Request $request, Form $form) // Route Model Binding
    {
        $teacher = Auth::user();

        // Autorisasi: Pastikan guru yang login adalah pemilik formulir
        if ($form->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'Unauthorized to update this form.'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'sometimes|array', // Pertanyaan bisa jadi tidak diupdate
            'questions.*.id' => 'nullable|integer|exists:questions,id,form_id,'.$form->id, // Jika question ID ada, harus ada di DB & milik form ini
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => ['required', Rule::in(['Text', 'MultipleChoice', 'Checkbox', 'LinearScale', 'file_upload', 'short_text', 'long_text', 'dropdown', 'true_false'])],
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*.id' => 'nullable|integer|exists:question_options,id', // Jika option ID ada
            'questions.*.options.*.option_text' => 'nullable|string|max:255',
            'questions.*.required' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            if ($request->has('title')) {
                $form->title = $validatedData['title'];
            }
            if ($request->has('description')) {
                $form->description = $validatedData['description'];
            }
            $form->save();

            if ($request->has('questions')) {
                $existingQuestionIds = [];
                foreach ($validatedData['questions'] as $questionData) {
                    $question = null;
                    if (!empty($questionData['id'])) {
                        $question = $form->questions()->find($questionData['id']);
                    }

                    if ($question) { // Update pertanyaan yang ada
                        $question->update([
                            'question_text' => $questionData['question_text'],
                            'question_type' => $this->mapMobileQuestionTypeToDb($questionData['question_type']),
                            'is_required' => $questionData['required'],
                        ]);
                    } else { // Buat pertanyaan baru
                        $question = $form->questions()->create([
                            'question_text' => $questionData['question_text'],
                            'question_type' => $this->mapMobileQuestionTypeToDb($questionData['question_type']),
                            'is_required' => $questionData['required'],
                        ]);
                    }
                    $existingQuestionIds[] = $question->id;

                    // Update atau buat opsi
                    if (in_array($question->question_type, ['multiple_choice', 'checkbox', 'dropdown']) && !empty($questionData['options'])) {
                        $existingOptionIds = [];
                        foreach ($questionData['options'] as $optionData) {
                             if (is_string($optionData)) { // Jika opsi adalah string sederhana (opsi baru)
                                if (!empty($optionData)) {
                                    $newOpt = $question->options()->create(['option_text' => $optionData]);
                                    $existingOptionIds[] = $newOpt->id;
                                }
                            } elseif (is_array($optionData) && !empty($optionData['option_text'])) { // Jika opsi adalah array (kemungkinan update)
                                $option = null;
                                if (!empty($optionData['id'])) {
                                    $option = $question->options()->find($optionData['id']);
                                }
                                if ($option) {
                                    $option->update(['option_text' => $optionData['option_text']]);
                                } else {
                                     $option = $question->options()->create(['option_text' => $optionData['option_text']]);
                                }
                                $existingOptionIds[] = $option->id;
                            }
                        }
                        // Hapus opsi yang tidak ada di request untuk pertanyaan ini
                        $question->options()->whereNotIn('id', $existingOptionIds)->delete();
                    } else {
                        // Hapus semua opsi jika tipe pertanyaan berubah atau tidak ada opsi
                        $question->options()->delete();
                    }
                }
                // Hapus pertanyaan yang tidak ada di request
                $form->questions()->whereNotIn('id', $existingQuestionIds)->delete();
            }

            DB::commit();
            $form->load(['teacher', 'questions.options']);
            return response()->json(new FormResource($form->fresh()));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update form: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus formulir.
     * Sesuai dengan DELETE /forms/{form}
     */
    public function apiDeleteForm(Request $request, Form $form) // Route Model Binding
    {
        $teacher = Auth::user();

        // Autorisasi: Pastikan guru yang login adalah pemilik formulir
        if ($form->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'Unauthorized to delete this form.'], 403);
        }

        // Relasi onDelete('cascade') pada migrasi questions dan question_options akan menghapus data terkait
        $form->delete();

        return response()->json(['message' => 'Form deleted successfully.']);
    }

}
