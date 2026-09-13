<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use App\Models\Room;
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
        $pairedHotelCategories = ['exterior-city', 'exterior-resort', 'lobby-modern', 'lobby-classic'];
        foreach (Hotel::whereIn('id', $ids)->with(['rooms' => fn ($q) => $q->orderBy('id')])->orderBy('id')->get() as $index => $hotel) {
            foreach (['exterior', $index % 2 ? 'exterior-resort' : 'exterior-city', 'lobby', $index % 2 ? 'lobby-classic' : 'lobby-modern', 'reception', 'pool', 'restaurant', 'breakfast', 'rooftop', 'spa', 'gym'] as $category) {
                if (empty($assets[$category])) continue;
                $assetIndex = in_array($category, $pairedHotelCategories, true) ? intdiv($index, 2) : $index;
                $asset = $assets[$category][$assetIndex % count($assets[$category])];
                $count += $this->attach($hotel, 'hotels', $asset);
            }
            foreach ($hotel->rooms as $r => $room) {
                $count += $this->reconcileDemoRoomImages($room, $assets);
            }
            $count += $this->reconcileDemoFeaturedImage($hotel, $assets);
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

    private function reconcileDemoFeaturedImage(Hotel $hotel, array $assets): int
    {
        $featured = $hotel->images()->where('is_featured', true)->first();
        $demoPrefix = 'hotels/'.$hotel->id.'/demo-';
        if ($featured && ! str_starts_with($featured->path, $demoPrefix)) return 0;

        $categories = ['exterior-city', 'exterior-resort', 'lobby-modern', 'lobby-classic', 'reception', 'rooftop', 'pool', 'restaurant'];
        $category = $categories[$this->stableIndex((string) $hotel->id, count($categories))];
        if (empty($assets[$category])) {
            $category = collect($categories)->first(fn ($name) => ! empty($assets[$name]));
        }
        if (! $category) return 0;

        $asset = $assets[$category][$this->stableIndex($hotel->id.':'.$category, count($assets[$category]))];
        $desiredPath = 'hotels/'.$hotel->id.'/'.$asset['name'];
        $added = 0;
        if (! $hotel->images()->where('path', $desiredPath)->exists()) {
            $added = $this->attach($hotel, 'hotels', $asset);
        }
        $hotel->images()->where('path', 'like', $demoPrefix.'%')->update(['is_featured' => false]);
        $hotel->images()->where('path', $desiredPath)->update(['is_featured' => true]);
        return $added;
    }

    private function reconcileDemoRoomImages(Room $room, array $assets): int
    {
        $plan = $this->roomImagePlan($room, $assets);
        $desiredPaths = collect($plan)->map(fn ($asset) => 'rooms/'.$room->id.'/'.$asset['name'])->values()->all();
        $demoPrefix = 'rooms/'.$room->id.'/demo-';
        $existing = $room->images()->where('path', 'like', $demoPrefix.'%')->orderBy('id')->get();
        $manualFeatured = $room->images()->where('is_featured', true)->where('path', 'not like', $demoPrefix.'%')->exists();

        if ($existing->pluck('path')->values()->all() === $desiredPaths) {
            if (! $manualFeatured && $desiredPaths) {
                $room->images()->where('path', 'like', $demoPrefix.'%')->update(['is_featured' => false]);
                $room->images()->where('path', $desiredPaths[0])->update(['is_featured' => true]);
            }
            return 0;
        }

        foreach ($existing as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $count = 0;
        foreach ($plan as $asset) $count += $this->attach($room, 'rooms', $asset);
        if (! $manualFeatured && $desiredPaths) {
            $room->images()->where('path', 'like', $demoPrefix.'%')->update(['is_featured' => false]);
            $room->images()->where('path', $desiredPaths[0])->update(['is_featured' => true]);
        }
        return $count;
    }

    private function roomImagePlan(Room $room, array $assets): array
    {
        $name = strtolower($room->name);
        $primary = str_contains($name, 'family') ? 'family' : (str_contains($name, 'suite') ? 'suite' : (str_contains($name, 'deluxe') ? 'deluxe' : (str_contains($name, 'twin') ? 'twin' : (str_contains($name, 'king') ? 'king' : 'standard'))));
        $roomPool = ['standard', 'deluxe', 'twin', 'king', 'suite', 'family'];
        $secondaryPool = array_values(array_filter($roomPool, fn ($category) => $category !== $primary && ! empty($assets[$category])));
        $secondary = $secondaryPool ? $secondaryPool[$this->stableIndex($room->id.':secondary', count($secondaryPool))] : null;
        $view = $this->stableIndex($room->id.':view', 2) ? 'sea-view' : 'city-view';
        $extras = ['bathroom', $view, 'balcony'];
        $rotation = $this->stableIndex($room->id.':extras', count($extras));
        $extras = array_merge(array_slice($extras, $rotation), array_slice($extras, 0, $rotation));
        $categories = array_values(array_filter(array_unique(array_merge([$primary, $secondary], $extras)), fn ($category) => ! empty($assets[$category])));

        return array_map(function ($category) use ($room, $assets) {
            return $assets[$category][$this->stableIndex($room->id.':'.$category, count($assets[$category]))];
        }, $categories);
    }

    private function stableIndex(string $seed, int $count): int
    {
        return $count > 0 ? abs(crc32($seed)) % $count : 0;
    }
}
