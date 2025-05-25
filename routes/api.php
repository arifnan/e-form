<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResponseController;

Route::get('/admins', [AdminController::class, 'apiIndex']);

// Route::get('/cek-api', function () {
//     return response()->json(['message' => 'API aktif']);
// });

Route::middleware('auth:sanctum')->group(function () {
    // API untuk siswa
    Route::get('/students', [StudentController::class, 'apiIndex']);

    // API untuk guru
    Route::get('/teachers', [TeacherController::class, 'apiIndex']);

    // API untuk form
    Route::get('/forms', [FormController::class, 'apiIndex']);
    Route::post('/forms', [FormController::class, 'apiStore']);
    Route::put('/forms/{form}', [FormController::class, 'apiUpdate']);
    Route::delete('/forms/{form}', [FormController::class, 'apiDestroy']);

    // API untuk question
    Route::get('/questions', [QuestionController::class, 'apiIndex']);
    Route::post('/questions', [QuestionController::class, 'apiStore']);
    Route::put('/questions/{question}', [QuestionController::class, 'apiUpdate']);
    Route::delete('/questions/{question}', [QuestionController::class, 'apiDestroy']);

    // API untuk response
    Route::get('/responses', [ResponseController::class, 'apiIndex']);
    Route::post('/responses', [ResponseController::class, 'apiStore']);
    Route::delete('/responses/{response}', [ResponseController::class, 'apiDestroy']);
});
