<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Voucher {{ $booking->booking_number }}</title>
<style>
body{font:15px/1.5 Arial,sans-serif;color:#182338;background:#eef1f5;margin:0;padding:32px}main{max-width:900px;margin:auto;background:white;padding:40px;border-top:8px solid #2563eb}h1{color:#2563eb;margin:0}h2{margin-top:24px}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:10px;border-bottom:1px solid #cbd5e1;vertical-align:top}small{color:#475569}.status{font-weight:bold;padding:12px;background:#eff6ff}tr{break-inside:avoid}@media print{body{background:white;padding:0}main{padding:16px}.screen{display:none}}@media(max-width:600px){body{padding:8px}main{padding:16px}table{font-size:12px}}
</style></head><body><main>
<h1>BookYourHotel</h1><p>Booking voucher · {{ $booking->booking_number }}</p>
<p class="screen">Print this document or choose “Save as PDF” in your browser print dialog. This download is HTML, not a pre-generated PDF.</p>
<p class="status">Booking: {{ ucfirst($booking->status->value) }} · Payment: {{ ucfirst($booking->payment?->status->value ?? 'not started') }} (simulation)</p>
@if($booking->isCancelled())<p><strong>CANCELLED — this reservation is no longer valid for a stay.</strong></p>@endif
<h2>{{ \App\Support\PublicLabel::clean($booking->hotel->name, 'Hotel') }}</h2><p>{{ $booking->hotel->address }}<br>{{ $booking->hotel->city }}, {{ $booking->hotel->country }}</p>
<p>Guest: <strong>{{ $booking->guest_name }}</strong><br>Check-in: {{ $booking->check_in->format('d.m.Y') }}<br>Check-out: {{ $booking->check_out->format('d.m.Y') }}<br>Nights: {{ (int) $booking->check_in->diffInDays($booking->check_out) }}</p>
<table><thead><tr><th>Room / board</th><th>Guests</th><th>Quantity / nights</th><th>Subtotal</th></tr></thead><tbody>
@foreach($booking->items as $item)<tr><td>{{ $item->room_name }}<br>{{ $item->board_name }}</td><td>{{ $item->adults }} adults<br>{{ $item->children }} children</td><td>{{ $item->quantity }} rooms<br>{{ $item->nights }} nights<br>{{ $item->check_in->format('d.m.Y') }}–{{ $item->check_out->format('d.m.Y') }}</td><td>{{ $item->currency }} {{ number_format($item->subtotal, 2) }}</td></tr>@endforeach
</tbody></table>
<h2>Total: {{ $booking->currency }} {{ number_format($booking->total, 2) }}</h2>
<p>Guest/owner cancellation deadline: {{ $booking->cancellationDeadline()->format('d.m.Y H:i') }} ({{ config('app.timezone') }}), the start of the day before check-in. Cancellation remains subject to the current booking status.</p>
@if($booking->payment?->status === \App\Enums\PaymentStatus::Refunded)<p>Refund recorded in the local payment simulation. No real bank refund has taken place.</p>@endif
@if($booking->isPending() && $booking->locked_until)<p>Unpaid hold deadline: {{ $booking->locked_until->format('d.m.Y H:i') }} ({{ config('app.timezone') }}).</p>@endif
<p>This voucher reflects the server state when generated. Pending is not confirmed; this document is not proof of a real payment. Expired, rejected and cancelled reservations are not valid for a stay.</p>
<small>Generated {{ $generatedAt->format('d.m.Y H:i:s') }} ({{ config('app.timezone') }}).</small>
</main></body></html>
