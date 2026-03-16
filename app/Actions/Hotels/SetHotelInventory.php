<?php

namespace App\Actions\Hotels;

use App\Models\RoomInventory;

class SetHotelInventory {
    public function execute($inventory, $room_id)
    {
        $data = [];

        foreach ($inventory as $item) {

            $data[] = [

                'room_id' => $room_id,

                'date' => $item['date'],

                'available' => $item['available'],

                'price' => $item['price'],

                'created_at' => now(),
                'updated_at' => now()

            ];

        }

        return RoomInventory::upsert(
            $data,
            ['room_id','date'],
            ['available','price','updated_at']
        );
    }
}