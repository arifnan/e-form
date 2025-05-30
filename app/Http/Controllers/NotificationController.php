<?php

namespace App\Http\Controllers; // Pastikan namespace ini benar

use Illuminate\Http\Request;
use App\Models\Notification; // Model Notification
use App\Http\Resources\NotificationResource; // Buat Resource ini
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang terautentikasi

class NotificationController extends Controller
{
    /**
     * Menampilkan notifikasi untuk pengguna yang terautentikasi.
     * Cocok untuk GET /notifications
     */
    public function apiGetUserNotifications(Request $request)
    {
        $user = Auth::user(); // Mendapatkan pengguna yang terautentikasi

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $notifications = $user->notifications() // Menggunakan relasi polimorfik
                               ->latest() // Urutkan dari yang terbaru
                               ->paginate(20); // Menggunakan paginasi

        return NotificationResource::collection($notifications);
    }

    /**
     * Menandai notifikasi spesifik sebagai sudah dibaca.
     * Cocok untuk PATCH /notifications/{notification}/read
     */
    public function apiMarkNotificationAsRead(Request $request, $notificationId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found or you are not authorized.'], 404);
        }

        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => new NotificationResource($notification->fresh()) // Kirim notifikasi yang sudah diupdate
        ]);
    }

    /**
     * Menandai semua notifikasi pengguna sebagai sudah dibaca.
     * Cocok untuk PATCH /notifications/mark-all-read
     */
    public function apiMarkAllNotificationsAsRead(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Laravel memiliki trait Notifiable yang menyediakan method unreadNotifications()
        // Pastikan model User Anda menggunakan trait Illuminate\Notifications\Notifiable
        if (method_exists($user, 'unreadNotifications')) {
            $user->unreadNotifications->markAsRead(); // Ini lebih efisien
        } else {
            // Fallback jika trait tidak digunakan atau method tidak ada (kurang efisien)
            $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        }


        return response()->json(['message' => 'All unread notifications marked as read.']);
    }


    /**
     * Menghapus notifikasi spesifik.
     * Cocok untuk DELETE /notifications/{notification}
     */
    public function apiDeleteNotification(Request $request, $notificationId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found or you are not authorized.'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully.']);
    }
}