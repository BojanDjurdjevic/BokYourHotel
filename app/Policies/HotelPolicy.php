<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Hotel;
use App\Models\User;

class HotelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role == User::ROLE_SUPPLIER;
    }

    public function view(User $user, Hotel $hotel): bool
    {
        return $user->id == $hotel->supplier_id;
    }

    public function create(User $user): bool
    {
        
        return $user->role == User::ROLE_SUPPLIER;
    }

    public function update(User $user, Hotel $hotel): bool
    {
        //dd(auth()user()->id(), $hotel->supplier_id);
        return $user->id == $hotel->supplier_id;
        //return true;
    }

    public function delete(User $user, Hotel $hotel): bool
    {
        return $user->id == $hotel->supplier_id;
    }

    public function restore(User $user, Hotel $hotel): bool
    {
        return false;
    }

    public function forceDelete(User $user, Hotel $hotel): bool
    {
        return false;
    }
}
