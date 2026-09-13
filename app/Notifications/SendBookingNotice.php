<?php
namespace App\Notifications;
use App\Events\BookingActivity;
use App\Enums\BookingNoticeType;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class SendBookingNotice
{
    public function handle(BookingActivity $event): void {
        try {
            $notice = new BookingNotice($event->data);
            if ($event->data['user_id'] !== null) {
                User::find($event->data['user_id'])?->notify($notice);
            } else {
                Notification::route('mail', $event->data['guest_email'])->notify($notice);
            }
            if (in_array($event->data['type'], [BookingNoticeType::BookingCreated->value, BookingNoticeType::BookingCancelled->value], true)
                && ($event->data['supplier_id'] ?? null) !== null
                && (int) $event->data['supplier_id'] !== (int) $event->data['user_id']) {
                User::find($event->data['supplier_id'])?->notify($notice);
            }
        } catch (\Throwable $e) {
            // Booking is already committed. Do not report a failed booking and invite a duplicate POST.
            report($e);
        }
    }
}
