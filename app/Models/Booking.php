<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Exceptions\BookingException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected static function booted(): void
    {
        static::deleting(function () {
            throw new BookingException('Bookings must be cancelled, not deleted.');
        });
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function ($query) use ($user) {
            $query->where('user_id', $user->id);

            if ($user->isSupplier()) {
                $query->orWhereHas('hotel', fn ($hotels) => $hotels->where('supplier_id', $user->id));
            }
        });
    }

    public function cancellationDeadline(): Carbon
    {
        return $this->check_in->copy()->startOfDay()->subDay();
    }

    public function canBeCancelledByGuest(): bool
    {
        return $this->canBeCancelled() && now()->lessThanOrEqualTo($this->cancellationDeadline());
    }

    public function canBeCompleted(): bool
    {
        return $this->isConfirmed() && now()->greaterThanOrEqualTo($this->check_out);
    }

    public function holdDeadlinePassed(): bool
    {
        return $this->isPending() && $this->locked_until !== null && now()->greaterThanOrEqualTo($this->locked_until);
    }

    protected $table = "bookings";

    protected $fillable = [
        'hotel_id',
        'user_id',

        'booking_number',

        'guest_name',
        'guest_email',
        'guest_phone',

        'check_in',
        'check_out',

        'subtotal',
        'discount',
        'tax',
        'total',

        'currency',

        'status',

        'locked_until',

        'notes',
    ];

    protected $casts = [
        'status' => BookingStatus::class,

        'check_in' => 'date',
        'check_out' => 'date',

        'locked_until' => 'datetime',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    /*
    public function room()
    {
        return $this->belongsTo(Room::class);
    } 

    public function bookingItem() : HasMany
    {
        return $this->hasMany(BookingItem::class);
    }*/

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function getNumberOfRoomsAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function getNumberOfGuestsAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->adults + $item->children;
        });
    }

    public function isPending(): bool
    {
        return $this->status === BookingStatus::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::Confirmed;
    }

    public function isCancelled(): bool
    {
        return $this->status === BookingStatus::Cancelled;
    }

    public function canBeCancelled(): bool
    {
        return $this->isPending()
            || $this->isConfirmed();
    }

    public function canBeConfirmed(): bool
    {
        return $this->isPending();
    }

    // Booking Number route:
    public function getRouteKeyName(): string
    {
        return 'booking_number';
    }
}
