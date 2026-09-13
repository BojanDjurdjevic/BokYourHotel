<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Models\User;

class BookingNotice extends Notification implements ShouldQueue
{
    use Queueable;
    public int $tries = 3;
    public function __construct(public array $data) { $this->afterCommit(); }
    public function via(object $notifiable): array { return $notifiable instanceof User ? ['database','mail'] : ['mail']; }
    public function toDatabase(object $notifiable): array { return $this->data; }
    public function toMail(object $notifiable): MailMessage {
        $guest = ! ($notifiable instanceof User);
        $parameters = ['booking' => $this->data['booking_number']];
        $expires = Carbon::parse($this->data['check_out'])->endOfDay();
        $manage = $guest ? URL::temporarySignedRoute('guest.bookings.show', $expires, $parameters) : route('bookings.show', $parameters);
        $voucher = $guest ? URL::temporarySignedRoute('guest.bookings.voucher', $expires, $parameters) : route('bookings.voucher', $parameters);
        $mail = (new MailMessage)->subject($this->data['type'].' — '.$this->data['booking_number'])
            ->view('emails.booking-notice', ['notice' => $this->data, 'manageUrl' => $manage, 'voucherUrl' => $voucher]);
        return $mail;
    }
}
