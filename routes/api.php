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
use App\Http\Controllers\NotificationController; // <-- Tambahkan ini
use App\Http\Controllers\FavoriteFormController; // <-- Tambahkan ini
use App\Models\Teacher;
use App\Http\Controllers\Api\AuthController;

// Rute Publik
Route::get('/admins', [AdminController::class, 'apiIndex']);
Route::get('/forms/code/{form_code}', [FormController::class, 'apiGetByFormCode']);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

    // Authentication Routes (Public)
    Route::post('/register/teacher', [AuthController::class, 'registerTeacher']);
    Route::post('/register/student', [AuthController::class, 'registerStudent']);
    Route::post('/login', [AuthController::class, 'loginUser']);

    // Publicly accessible form by code
    Route::get('/forms/code/{form_code}', [FormController::class, 'apiGetByFormCode']);

    // Authenticated Routes (Protected by Sanctum middleware)
    Route::middleware('auth:sanctum')->group(function () {
    // Auth specific routes
   Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logoutUser']);
    Route::get('/user', [App\Http\Controllers\Api\AuthController::class, 'getAuthenticatedUser']);

    // User Profile Update
    Route::put('/profile', [UserController::class, 'apiUpdateUserProfile']);

    // Form Management (Primarily for Teachers, namun GET bisa juga untuk Student jika ada form publik)
    // Untuk GET /forms, ApiService.kt memiliki getTeacherForms() dan juga ada referensi
    // untuk siswa melihat daftar form. Kita akan gunakan method yang lebih spesifik
    // jika logika di controller berbeda untuk guru dan siswa.
    // Jika sama, satu method 'apiIndex' di FormController bisa menangani berdasarkan peran user.
    // Untuk saat ini, kita akan sesuaikan dengan yang lebih eksplisit dari ApiService.kt.
    Route::get('/forms', [FormController::class, 'apiGetTeacherForms']); // Endpoint untuk guru mengambil daftar formnya
                                                                      // Jika siswa juga bisa GET /forms, pastikan controllernya bisa membedakan
    Route::post('/forms', [FormController::class, 'apiCreateForm']);    // Membuat form baru (oleh guru)
    Route::get('/forms/{form}', [FormController::class, 'apiGetFormDetails']); // Detail form
    Route::put('/forms/{form}', [FormController::class, 'apiUpdateForm']);     // Update form (oleh guru)
    Route::delete('/forms/{form}', [FormController::class, 'apiDeleteForm']);   // Hapus form (oleh guru)

    // Form Responses
    Route::post('/responses', [ResponseController::class, 'apiSubmitFormResponse']);      // Siswa mengirim jawaban
    Route::get('/forms/{form}/responses', [ResponseController::class, 'apiGetResponsesForForm']); // Guru melihat jawaban untuk form tertentu

    // History Routes
    Route::get('/teacher/forms/history', [TeacherController::class, 'apiGetTeacherFormsHistory']);
    Route::get('/student/responses/history', [StudentController::class, 'apiGetStudentSubmittedResponsesHistory']);

    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'apiGetUserNotifications']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'apiMarkNotificationAsRead']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'apiMarkAllNotificationsAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'apiDeleteNotification']);

    // Favorite Forms Routes
    Route::get('/favorites/forms', [FavoriteFormController::class, 'apiGetUserFavoriteForms']);
    Route::post('/forms/{form}/toggle-favorite', [FavoriteFormController::class, 'apiToggleFavoriteForm']);
});