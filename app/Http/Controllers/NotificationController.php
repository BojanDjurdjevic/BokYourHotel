<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class NotificationController extends Controller
{
    public function index(Request $request) {
        return view('notifications.index', ['notifications' => $request->user()->notifications()->latest()->paginate(15)]);
    }
    public function read(Request $request, string $notification) {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();
        return back()->with('success', 'Notification marked as read.');
    }
}
