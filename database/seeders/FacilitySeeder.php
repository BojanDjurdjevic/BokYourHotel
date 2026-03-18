<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            ['name'=>'WiFi','icon'=>'wifi','category'=>'room'],
            ['name'=>'TV','icon'=>'tv','category'=>'room'],
            ['name'=>'Air Conditioning','icon'=>'air_conditioning','category'=>'room'],
            ['name'=>'Balcony','icon'=>'balcony','category'=>'outdoor'],
        ];

        foreach ($facilities as $facility) {
            Facility::create([
                'name' => $facility['name'],
                'icon' => $facility['icon'],
                'category' => $facility['category']
            ]);
        }

        
    }
}
