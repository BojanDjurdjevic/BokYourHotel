<!doctype html><html lang="en"><head><meta charset="utf-8"><title>{{ $notice['type'] }}</title></head>
<body style="font-family:Arial,sans-serif;color:#172033;max-width:640px;margin:24px auto;padding:24px">
<h1>BookYourHotel</h1><h2>{{ $notice['type'] }}</h2>
<p>Booking reference: <strong>{{ $notice['booking_number'] }}</strong></p>
<p>Hotel: {{ \App\Support\PublicLabel::clean($notice['hotel'], 'Hotel') }}</p>
<p>Booking status at {{ $notice['occurred_at'] }}: {{ $notice['status'] }}</p>
<p>Payment: {{ $notice['payment_status'] }} (simulation)</p>
@if($notice['reason'])<p>Cancellation reason: {{ $notice['reason'] }}</p>@endif
@if($notice['payment_status'] === 'refunded')<p>Refund recorded in the local payment simulation. No bank transfer or real refund has taken place.</p>@endif
<p><a href="{{ $manageUrl }}">View booking</a> · <a href="{{ $voucherUrl }}">Download voucher</a></p>
<p>These links show the current reservation state. A voucher is not proof of payment or hotel confirmation.</p>
@if($notice['user_id'] === null)<p>Keep these private signed links safe. They expire at the end of your check-out day. Anyone with the links can access your booking.</p>@endif
</body></html>
