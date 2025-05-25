<?
namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Teacher;
use Illuminate\Http\Request;

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
}
