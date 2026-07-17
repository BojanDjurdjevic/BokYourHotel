<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function create(array $data): Booking
    {

    }

    private function buildPeriod(Carbon $checkIn, Carbon $checkOut): Collection
    {

    }

    private function loadRooms(array $items): EloquentCollection
    {

    }

    private function loadInventories(EloquentCollection $rooms, Collection $period): Collection
    {

    }

    private function ensureRoomsBelongToHotel(EloquentCollection $rooms, int $hotelId): void
    {

    }

    private function ensureBoardTypes(EloquentCollection $rooms, array $items): void
    {

    }

    private function ensureAvailability(EloquentCollection $rooms, Collection $inventories, Collection $period, array $items): void
    {

    }

    private function calculateTotals(EloquentCollection $rooms, array $items, Collection $period): array
    {

    }

    private function createBooking(array $data, array $totals): Booking
    {

    }

    private function createBookingItems(Booking $booking, EloquentCollection $rooms, array $items, Collection $period): void
    {

    }

    private function decreaseAvailability(Collection $inventories, Collection $period, array $items): void
    {

    }

    private function generateBookingNumber(): string
    {
        
    }
}