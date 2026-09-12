<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DemoImages extends Command
{
    protected $signature = 'demo:images';
    protected $description = 'Import only approved, locally present demo image assets';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) return self::FAILURE;
        $run = DB::table('demo_seed_runs')->where('name', 'portfolio-v1')->first();
        if (! $run) { $this->error('Run demo:seed first.'); return self::FAILURE; }
        $directory = config('demo.asset_directory', resource_path('demo'));
        $manifest = json_decode(file_get_contents($directory.'/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
        validator($manifest, ['categories' => ['required','array'], 'categories.*' => ['string'], 'assets' => ['present','array'], 'assets.*.filename' => ['required','string'], 'assets.*.category' => ['required','string']])->validate();
        $root = realpath($directory.'/images');
        $assets = [];
        $found = 0;
        foreach ($manifest['assets'] as $asset) {
            $file = $root ? realpath($root.DIRECTORY_SEPARATOR.($asset['filename'] ?? '')) : false;
            if ($file && is_file($file) && str_starts_with($file, $root.DIRECTORY_SEPARATOR)) $found++;
            if (! $file || ! str_starts_with($file, $root.DIRECTORY_SEPARATOR) || ! is_file($file)
                || ($asset['approved'] ?? false) !== true || empty($asset['license_note'])
                || ! in_array($asset['category'] ?? '', $manifest['categories'], true)
                || filesize($file) > 8 * 1024 * 1024) continue;
            if (($manifest['version'] ?? 1) >= 2 && !in_array($asset['source_type'] ?? '', ['generated-demo-asset','local-original','licensed-local'], true)) continue;
            $info = @getimagesize($file);
            if (! $info || $info[0] * $info[1] > 40000000 || ! in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) continue;
            $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime']];
            $assets[$asset['category']][] = ['file' => $file, 'name' => 'demo-'.hash_file('sha256', $file).'.'.$extension];
        }
        $count = 0;
        $ids = json_decode($run->summary, true)['hotel_ids'];
        foreach (Hotel::whereIn('id', $ids)->with(['rooms' => fn ($q) => $q->orderBy('id')])->orderBy('id')->get() as $index => $hotel) {
            foreach (['exterior', $index % 2 ? 'exterior-resort' : 'exterior-city', 'lobby', $index % 2 ? 'lobby-classic' : 'lobby-modern', 'reception', 'pool', 'restaurant', 'breakfast', 'rooftop', 'spa', 'gym'] as $category) {
                if (empty($assets[$category])) continue;
                $asset = $assets[$category][$index % count($assets[$category])];
                $count += $this->attach($hotel, 'hotels', $asset);
            }
            foreach ($hotel->rooms as $r => $room) {
                $roomCategory = str_contains(strtolower($room->name), 'family') ? 'family' : (str_contains(strtolower($room->name), 'suite') ? 'suite' : ($r === 0 ? 'standard' : 'deluxe'));
                foreach ([$roomCategory, $r === 0 ? 'standard-room' : ($r === 1 ? 'deluxe-room' : 'suite'), $r % 2 ? 'twin' : 'king', 'bathroom', $index % 2 ? 'sea-view' : 'city-view', 'balcony'] as $category) {
                    if (empty($assets[$category])) continue;
                    $count += $this->attach($room, 'rooms', $assets[$category][($index + $r) % count($assets[$category])]);
                }
            }
        }
        $populatedHotels = DB::table('hotel_images')->whereIn('hotel_id', $ids)->where('path', 'like', 'hotels/%/demo-%')->distinct()->count('hotel_id');
        $populatedRooms = DB::table('room_images')->join('rooms', 'rooms.id', '=', 'room_images.room_id')->whereIn('rooms.hotel_id', $ids)
            ->where('room_images.path', 'like', 'rooms/%/demo-%')->distinct()->count('room_images.room_id');
        $this->info($found.'/'.count($manifest['assets']).' assets found; '.array_sum(array_map('count', $assets)).' approved valid assets.');
        $this->info($populatedHotels.' hotels populated; '.$populatedRooms.' rooms populated.');
        $this->line('Missing categories: '.implode(', ', array_diff($manifest['categories'], array_keys($assets))));
        $this->info('New local image relations: '.$count.'. Missing/unapproved assets are skipped.');
        return self::SUCCESS;
    }

    private function attach($model, string $directory, array $asset): int
    {
        $path = $directory.'/'.$model->id.'/'.$asset['name'];
        if ($model->images()->where('path', $path)->exists()) return 0;
        if (! Storage::disk('public')->put($path, file_get_contents($asset['file']))) throw new \RuntimeException('Demo asset write failed.');
        try {
            $model->images()->create(['path' => $path, 'is_featured' => ! $model->images()->where('is_featured', true)->exists()]);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }
        return 1;
    }
}
