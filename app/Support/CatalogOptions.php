<?php

namespace App\Support;

use App\Models\BoardType;
use App\Models\Facility;
use Illuminate\Support\Collection;

final class CatalogOptions
{
    public static function boards(): Collection
    {
        return BoardType::query()
            ->whereNull('archived_at')
            ->get(['id', 'code', 'name'])
            ->map(function ($board) {
                $board->semantic_key = CatalogLabel::key($board->name);
                $board->label = CatalogLabel::label($board->name);
                return $board;
            })
            ->sortBy(fn ($board) => ($board->code && str_starts_with($board->code, 'DEMO-') ? '1' : '0').$board->semantic_key.$board->id)
            ->unique('semantic_key')
            ->sortBy('label')
            ->values();
    }

    public static function facilities(): Collection
    {
        return Facility::query()
            ->get(['id', 'name', 'icon', 'category'])
            ->map(function ($facility) {
                $facility->semantic_key = FacilityLabel::key($facility->name);
                $facility->label = FacilityLabel::label($facility->name);
                return $facility;
            })
            ->sortBy(fn ($facility) => (str_starts_with(strtolower($facility->name), 'demo ') ? '1' : '0').$facility->semantic_key.$facility->id)
            ->unique('semantic_key')
            ->sortBy('label')
            ->values();
    }

    public static function ensureBoard(string $name, string $code): int
    {
        $board = self::boards()->firstWhere('semantic_key', CatalogLabel::key($name));
        if ($board) return $board->id;

        return BoardType::query()->create([
            'code' => $code,
            'name' => CatalogLabel::label($name),
        ])->id;
    }

    public static function ensureFacility(string $name): int
    {
        $facility = self::facilities()->firstWhere('semantic_key', FacilityLabel::key($name));
        if ($facility) return $facility->id;

        return Facility::query()->create([
            'name' => FacilityLabel::canonicalValue($name),
        ])->id;
    }
}
