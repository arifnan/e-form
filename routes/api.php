<?php

use Illuminate\Http\Request; // <--- Tambahkan ini
use Illuminate\Support\Facades\Auth; // <--- Tambahkan ini
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\UserController;

// Rute Publik
Route::get('/admins', [AdminController::class, 'apiIndex']);
Route::get('/forms/code/{form_code}', [FormController::class, 'apiGetByFormCode']);

// Rute Registrasi API untuk Guru dan Murid
Route::post('/register/teacher', [TeacherController::class, 'apiRegister']);
Route::post('/register/student', [StudentController::class, 'apiRegister']);

// Endpoint publik untuk mengambil form berdasarkan kode
Route::get('/forms/code/{form_code}', [FormController::class, 'apiGetByFormCode']);

Route::middleware('auth:sanctum')->group(function () {
    // API untuk update profil pengguna (Admin, Teacher, Student)
    Route::put('/profile', [UserController::class, 'updateProfile']);

    // API untuk siswa
    Route::get('/students', [StudentController::class, 'apiIndex']);
    Route::get('/student/responses/history', [StudentController::class, 'apiGetResponseHistory']);

    // API untuk guru
    Route::get('/teachers', [TeacherController::class, 'apiIndex']);
    Route::get('/teacher/forms/history', [TeacherController::class, 'apiGetFormHistory']);

    // API untuk form (CRUD yang sudah ada)
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
    
    // Modifikasi apiStore di ResponseController untuk menangani student_id dari user yang terautentikasi
    Route::post('/responses', function (Request $request) { // Sekarang $request akan dikenali tipenya
        $controller = new ResponseController();
        
        // Cek jika user adalah student dan tambahkan student_id ke request
        if (Auth::check() && Auth::user() instanceof \App\Models\Student) { // Auth juga akan dikenali
            $request->merge(['student_id' => Auth::id()]);
        }
        // Validasi di dalam controller akan menangani jika student_id ada atau tidak
        return $controller->apiStore($request);
    });
    Route::delete('/responses/{response}', [ResponseController::class, 'apiDestroy']);
});