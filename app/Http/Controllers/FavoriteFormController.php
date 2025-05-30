<?php

namespace App\Http\Controllers; // Pastikan namespace ini benar

use Illuminate\Http\Request;
use App\Models\Form;
use App\Http\Resources\FormResource; // Gunakan FormResource yang sudah ada atau buat yang baru
use Illuminate\Support\Facades\Auth;

class FavoriteFormController extends Controller
{
    /**
     * Menampilkan formulir yang difavoritkan oleh pengguna.
     * Cocok untuk GET /favorites/forms
     */
    public function apiGetUserFavoriteForms(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Mengambil formulir yang difavoritkan oleh pengguna melalui relasi polimorfik
        $favoriteForms = $user->favoriteForms()
                               ->with(['teacher', 'questions' => function ($query) { // Eager load relasi
                                   $query->with('options'); // Jika Question punya relasi options
                               }])
                               ->latest('favorite_forms.created_at') // Urutkan berdasarkan kapan difavoritkan
                               ->paginate(10);

        return FormResource::collection($favoriteForms);
    }

    /**
     * Menambah atau menghapus formulir dari daftar favorit pengguna.
     * Cocok untuk POST /forms/{form}/toggle-favorite
     */
    public function apiToggleFavoriteForm(Request $request, Form $form) // Menggunakan Route Model Binding untuk $form
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Method toggle akan melakukan attach jika belum ada, dan detach jika sudah ada.
        // Ini adalah fitur dari relasi MorphToMany / BelongsToMany.
        $user->favoriteForms()->toggle([$form->id]);

        // Cek status favorit saat ini setelah operasi toggle
        $isFavorited = $user->favoriteForms()->where('forms.id', $form->id)->exists();

        $message = $isFavorited ? 'Form added to favorites.' : 'Form removed from favorites.';

        return response()->json([
            'message' => $message,
            'form_id' => $form->id,
            'is_favorited' => $isFavorited
        ]);
    }
}