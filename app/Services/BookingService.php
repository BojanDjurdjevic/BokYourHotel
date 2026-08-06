<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\BookingException;
use App\Models\BoardType;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomInventory;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function create(array $data): Booking
    {
        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);

        $this->validatePeriod($checkIn, $checkOut);

        $period = $this->buildPeriod($checkIn, $checkOut);

        $rooms = $this->loadRooms($data['items']);

        $this->ensureRoomsBelongToHotel(
            $rooms,
            $data['hotel_id']
        );

        $inventories = $this->loadInventories(
            $rooms,
            $period
        );

        $this->ensureAvailability(
            $rooms,
            $inventories,
            $period,
            $data['items']
        );

        $totals = $this->calculateTotals(
            $rooms,
            $inventories,
            $data['items'],
            $period
        );

        return DB::transaction(function () use (
            $data,
            $totals,
            $rooms,
            $inventories,
            $period
        ) {

            $booking = $this->createBooking(
                $data,
                $totals
            );

            $this->createBookingItems(
                $booking,
                $rooms,
                $totals['items']
            );

            $this->decreaseAvailability(
                $inventories,
                $period,
                $data['items'],
                $rooms
            );

            return $booking;
        });
    }

    private function validatePeriod(Carbon $checkIn, Carbon $checkOut): void
    {
        if ($checkIn->isPast()) {
            throw new BookingException(
                'Check-in date cannot be in the past.'
            );
        }

        if ($checkOut <= $checkIn) {
            throw new BookingException(
                'Check-out date must be after check-in.'
            );
        }

        if ($checkIn->diffInDays($checkOut) > 30) {
            throw new BookingException(
                'The maximum stay is 30 days.'
            );
        }
    }

    private function buildPeriod(Carbon $checkIn, Carbon $checkOut): Collection
    {
        $period = collect();

        for ($date = $checkIn->copy(); $date < $checkOut; $date->addDay()) {
            $period->push($date->copy());
        }

        return $period;
    }

    private function loadRooms(array $items): EloquentCollection
    {
        $roomIds = collect($items)
            ->pluck('room_id')
            ->unique();

        return Room::query()
            ->whereIn('id', $roomIds)
            ->with('boardTypes')
            ->get()
            ->keyBy('id');
    }

    private function loadInventories(EloquentCollection $rooms, Collection $period): Collection
    {
        $inventories = RoomInventory::query()
        ->whereIn('room_id', $rooms->pluck('id'))
        ->whereBetween('date', [$period->first()->toDateString(), $period->last()->toDateString()])
        ->get()
        ->groupBy('room_id')
        ->map(function ($items) {
            return $items->keyBy(function ($inventory) {
                return $inventory->date->format('Y-m-d');
            });
        });

        return $inventories;
    }

    private function ensureRoomsBelongToHotel(EloquentCollection $rooms, int $hotelId): void
    {
        if(!$rooms->every(
            fn($room) => $room->hotel_id === $hotelId 
        )) {
            throw new BookingException('Selected rooms do not belong to this hotel!');
        }
    }

    private function findBoardType(Room $room, int $boardTypeId): BoardType
    {
        $boardType = $room->boardTypes
            ->firstWhere('id', $boardTypeId);

        if (! $boardType) {
            throw new BookingException(
                'Selected board type does not belong to selected room.'
            );
        }

        return $boardType;
    }

    private function ensureAvailability(EloquentCollection $rooms, Collection $inventories, Collection $period, array $items): void
    {
        foreach ($items as $item) {

            $room = $rooms[$item['room_id']];

            $roomInventories = $inventories[$room->id] ?? collect();

            foreach ($period as $date) {

                $inventory = $roomInventories
                    ->get($date->toDateString());

                $available = $inventory
                    ? $inventory->available
                    : $room->total_units;

                if ($available < $item['quantity']) {

                    throw new BookingException(
                        "Room {$room->name} does not have enough availability."
                    );

                }
            }
        }
    }

    private function calculateTotals(EloquentCollection $rooms, Collection $inventories, array $items, Collection $period): array
    {
        $preparedItems = [];
        $grandTotal = 0;

        foreach ($items as $item) {

            $roomInventories = $inventories[$item['room_id']];

            $room = $rooms[$item['room_id']];

            $board = $this->findBoardType(
                $room,
                $item['board_type_id']
            );
            
            $roomTotal = 0;
            $boardTotal = 0;
            $totalPerUnit = 0;
            $itemTotal = 0;

            foreach ($period as $date) {

                $inventory = $roomInventories[$date->toDateString()] ?? null;

                $roomTotal += $inventory?->price ?? $room->price_per_night;

                $boardTotal += $board->pivot->price;
            }

            $totalPerUnit = $roomTotal + $boardTotal;
            $itemTotal = $totalPerUnit * $item['quantity'];
            $grandTotal += $itemTotal;
            /*
            $bookingItems[] = [
                'room_id' => $room->id,
                'board_type_id' => $board->id,
                'quantity' => $item['quantity'],

                'subtotal' => $itemTotal,
                'room_total' => $roomTotal,
                'board_total' => $boardTotal,

                'nights' => $period->count()
            ]; 

            $preparedItems[] = [

                'room_id'         => $room->id,
                'board_type_id'   => $board->id,

                'quantity'        => $item['quantity'],
                'adults'          => $item['adults'],
                'children'        => $item['children'],

                'price_per_night' => $totalPerUnit / $period->count(),

                'subtotal'        => $itemTotal,

                'nights'          => $period->count(),

                'check_in'        => $period->first(),
                'check_out'       => $period->last()->copy()->addDay(),

                'currency'        => 'EUR',
            ];*/

            $preparedItems[] = [

                'room_id' => $room->id,

                'board_type_id' => $board->id,

                'room_name' => $room->name,

                'board_name' => $board->name,

                'quantity' => $item['quantity'],

                'adults' => $item['adults'],

                'children' => $item['children'],

                'price_per_night' => $totalPerUnit / $period->count(),

                'subtotal' => $itemTotal,

                'nights' => $period->count(),

                'check_in' => $period->first(),

                'check_out' => $period->last()->copy()->addDay(),

                'currency' => 'EUR',
            ];

        }

        return [

            'items' => $preparedItems,

            'subtotal' => $grandTotal,

            'discount' => 0,

            'tax' => 0,

            'total' => $grandTotal,

        ];
    }

    private function createBooking(array $data, array $totals): Booking
    {
        return Booking::create([

            'hotel_id' => $data['hotel_id'],

            'booking_number' => $this->generateBookingNumber(),

            'guest_name' => $data['guest_name'],

            'guest_email' => $data['guest_email'],

            'guest_phone' => $data['guest_phone'],

            'notes' => $data['notes'],

            'status' => BookingStatus::Pending,

            'subtotal' => $totals['subtotal'],

            'discount' => $totals['discount'],

            'tax' => $totals['tax'],

            'total' => $totals['total'],

            'currency' => 'EUR',

            'check_in' => $data['check_in'],

            'check_out' => $data['check_out'],

        ]);
    }

    private function createBookingItems(Booking $booking, Collection $items): void
    {
        $booking->items()->createMany($items);
    }

    private function decreaseAvailability(Collection $inventories, Collection $period,  array $items, EloquentCollection $rooms): void
    {
        foreach ($items as $item) {

            $room = $rooms[$item['room_id']];

            $roomInventories = $inventories[$room->id] ?? collect();

            foreach ($period as $date) {

                $inventory = $roomInventories
                    ->get($date->toDateString());

                if (!$inventory) {

                    $inventory = RoomInventory::create([

                        'room_id' => $room->id,

                        'date' => $date,

                        'available' =>
                            $room->total_units,

                        'price' =>
                            $room->price_per_night

                    ]);

                    $roomInventories->put(
                        $date->toDateString(),
                        $inventory
                    );
                }

                $inventory->decrement(
                    'available',
                    $item['quantity']
                );
            }
        }
    }

    private function generateBookingNumber(): string
    {
        do {

            $number =
                'BYH-'
                . now()->format('ymd')
                . '-'
                . strtoupper(
                    \Illuminate\Support\Str::random(6)
                );

        } while (

            Booking::where(
                'booking_number',
                $number
            )->exists()

        );

        return $number;
    }
}