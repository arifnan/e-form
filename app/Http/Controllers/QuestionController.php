<?
namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Form;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::with('form')->get();
        return view('questions.index', compact('questions'));
    }

    public function create()
    {
        $forms = Form::all();
        return view('questions.create', compact('forms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'question_text' => 'required',
            'question_type' => 'required',
            'is_required' => 'boolean',
        ]);

        Question::create($data);
        return redirect()->route('questions.index')->with('success', 'Question created.');
    }

    public function edit(Question $question)
    {
        $forms = Form::all();
        return view('questions.edit', compact('question', 'forms'));
    }

    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'question_text' => 'required',
            'question_type' => 'required',
            'is_required' => 'boolean',
        ]);

        $question->update($data);
        return redirect()->route('questions.index')->with('success', 'Question updated.');
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('questions.index')->with('success', 'Question deleted.');
    }

    // API
    public function apiIndex()
    {
        return response()->json(Question::with('form')->get());
    }

    public function apiStore(Request $request)
    {
        $data = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'question_text' => 'required',
            'question_type' => 'required',
            'is_required' => 'boolean',
        ]);

        $question = Question::create($data);
        return response()->json($question, 201);
    }

    public function apiUpdate(Request $request, Question $question)
    {
        $data = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'question_text' => 'required',
            'question_type' => 'required',
            'is_required' => 'boolean',
        ]);

        $question->update($data);
        return response()->json($question);
    }

    public function apiDestroy(Question $question)
    {
        $question->delete();
        return response()->json(['message' => 'Question deleted']);
    }
}
