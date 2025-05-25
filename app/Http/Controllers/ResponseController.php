<?
namespace App\Http\Controllers;

use App\Models\Response;
use App\Models\ResponseAnswer;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function index()
    {
        $responses = Response::with('answers')->get();
        return view('responses.index', compact('responses'));
    }

    public function destroy(Response $response)
    {
        $response->delete();
        return redirect()->route('responses.index')->with('success', 'Response deleted.');
    }

    // API
    public function apiIndex()
    {
        return response()->json(Response::with('answers')->get());
    }

    public function apiStore(Request $request)
    {
        $data = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.option_id' => 'nullable|exists:question_options,id',
            'answers.*.file_url' => 'nullable|string',
            'answers.*.latitude' => 'nullable|numeric',
            'answers.*.longitude' => 'nullable|numeric',
            'answers.*.formatted_address' => 'nullable|string',
        ]);

        $response = Response::create(['form_id' => $request->form_id]);

        foreach ($data['answers'] as $answer) {
            ResponseAnswer::create([
                'response_id' => $response->id,
                'question_id' => $answer['question_id'],
                'answer_text' => $answer['answer_text'] ?? null,
                'option_id' => $answer['option_id'] ?? null,
                'file_url' => $answer['file_url'] ?? null,
                'latitude' => $answer['latitude'] ?? null,
                'longitude' => $answer['longitude'] ?? null,
                'formatted_address' => $answer['formatted_address'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Jawaban berhasil disimpan'], 201);
    }

    public function apiDestroy(Response $response)
    {
        $response->delete();
        return response()->json(['message' => 'Response deleted']);
    }
}
