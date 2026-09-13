<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Hotel;
use App\Support\PublicLabel;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function getAvailability(
        Hotel $hotel,
        Carbon $checkIn,
        Carbon $checkOut
    ): array {
        $checkIn = $checkIn->copy()->startOfDay();
        $checkOut = $checkOut->copy()->startOfDay();

        $this->validatePeriod(
            $checkIn,
            $checkOut
        );

        $period = $this->buildPeriod(
            $checkIn,
            $checkOut
        );

        $rooms = $hotel->rooms()
            ->with([
                'featuredImage',
                'boardTypes',
                'roomType',
            ])
            ->get();

        $inventories = $this->loadInventories(
            $rooms,
            $period
        );

        $availableRooms = $rooms
            ->map(function ($room) use (
                $inventories,
                $period
            ) {

                $roomInventories =
                    $inventories[$room->id]
                    ?? collect();

                /*
                 * A room can only be booked if it is
                 * available for the entire period.
                 */
                $minimumAvailable = $period
                    ->map(function ($date) use (
                        $room,
                        $roomInventories
                    ) {

                        $inventory =$roomInventories->get($date->toDateString());

                        return $inventory ? $inventory->available : $room->total_units;

                    })->min();

                /*
                 * Calculate room price for the period.
                 */
                $roomTotal = $period->sum(function ($date) use (
                        $room,
                        $roomInventories
                    ) {

                        $inventory = $roomInventories->get($date->toDateString());

                        return $inventory?->price ?? $room->price_per_night;

                    });

                return [

                    'id' => $room->id,

                    'name' => $room->name,

                    'description' => $room->description,

                    'capacity' => $room->capacity,

                    'total_units' => $room->total_units,

                    /*
                     * Lowest availability during stay.
                     *
                     * Example:
                     *
                     * Day 1 = 5
                     * Day 2 = 2
                     * Day 3 = 4
                     *
                     * User can book maximum 2 rooms.
                     */
                    'available' => $minimumAvailable,

                    'room_total' => $roomTotal,

                    'image' =>
                        $room->featuredImage
                            ? asset(
                                'storage/' .
                                $room->featuredImage->path
                            )
                            : null,

                    'room_type' => PublicLabel::clean($room->roomType?->name),

                    'board_types' =>
                        $room->boardTypes
                            ->map(function ($board) use (
                                $period
                            ) {

                                /*
                                 * Board price is currently
                                 * price per night.
                                 */
                                $boardPricePerNight =
                                    $board->pivot->price;

                                $boardTotal =
                                    $boardPricePerNight
                                    * $period->count();

                                return [

                                    'id' => $board->id,

                                    'name' => $board->name,

                                    'price_per_night' => $boardPricePerNight,

                                    'total' => $boardTotal,

                                ];

                            })->values(),

                ];

            })->filter(
                fn ($room) =>
                    $room['available'] > 0
            )->values();

        return [

            'check_in' => $checkIn->toDateString(),

            'check_out' => $checkOut->toDateString(),

            'nights' => $period->count(),

            'rooms' => $availableRooms,

        ];
    }

    private function validatePeriod(
        Carbon $checkIn,
        Carbon $checkOut
    ): void {

        if ($checkIn->lessThan(Carbon::today())) {

            throw new BookingException(
                'Check-in date cannot be in the past.'
            );

        }

        if ($checkOut->lessThanOrEqualTo($checkIn)) {

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

    private function buildPeriod(
        Carbon $checkIn,
        Carbon $checkOut
    ): Collection {

        $period = collect();

        for ($date = $checkIn->copy(); $date < $checkOut; $date->addDay()) {

            $period->push($date->copy());

        }

        return $period;
    }

    private function loadInventories(
        Collection $rooms,
        Collection $period
    ): Collection {

        return \App\Models\RoomInventory::query()
            ->whereIn(
                'room_id',
                $rooms->pluck('id')
            )
            ->whereBetween(
                'date',
                [
                    $period->first()->toDateString(),

                    $period->last()->toDateString(),
                ]
            )
            ->get()
            ->groupBy('room_id')
            ->map(function ($items) {

                return $items->keyBy(
                    fn ($inventory) =>$inventory->date->toDateString()
                );

            });
    }
}
