<?php

namespace App\Console\Commands;

use App\Support\CatalogLabel;
use App\Support\FacilityLabel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoCatalog extends Command
{
    protected $signature = 'demo:catalog {--apply : Apply the local/demo-only catalog cleanup}';
    protected $description = 'Audit or clean semantic duplicates in the local demo catalog';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('This command is limited to local and testing environments.');
            return self::FAILURE;
        }

        if (! $this->option('apply')) {
            $this->line('Dry run only. Add --apply to remap demo pivots, archive duplicate boards and remove duplicate demo facilities.');
            $this->printGroups();
            return self::SUCCESS;
        }

        $result = DB::transaction(function (): array {
            return [
                'boards' => $this->normalizeBoards(),
                'facilities' => $this->normalizeFacilities(),
                'hotels' => $this->normalizeDemoHotelFacilities(),
            ];
        });

        $this->info('Catalog cleanup applied without changing bookings, payments or booking item snapshots.');
        $this->line('Archived duplicate board types: '.$result['boards']['archived']);
        $this->line('Remapped room-board pivots: '.$result['boards']['pivots']);
        $this->line('Removed duplicate demo facilities: '.$result['facilities']['removed']);
        $this->line('Remapped room-facility pivots: '.$result['facilities']['pivots']);
        $this->line('Normalized demo hotel facility lists: '.$result['hotels']);

        return self::SUCCESS;
    }

    private function printGroups(): void
    {
        foreach (DB::table('board_types')->orderBy('id')->get(['id', 'code', 'name', 'archived_at'])->groupBy(fn ($row) => CatalogLabel::key($row->name)) as $key => $group) {
            if ($group->count() > 1) $this->line('Board '.$key.': '.$group->map(fn ($row) => "#{$row->id} {$row->name}")->implode(', '));
        }
        foreach (DB::table('facilities')->orderBy('id')->get(['id', 'name'])->groupBy(fn ($row) => FacilityLabel::key($row->name)) as $key => $group) {
            if ($group->count() > 1) $this->line('Facility '.$key.': '.$group->map(fn ($row) => "#{$row->id} {$row->name}")->implode(', '));
        }
    }

    private function normalizeBoards(): array
    {
        $archived = 0;
        $pivots = 0;
        $groups = DB::table('board_types')->orderBy('id')->get(['id', 'code', 'name', 'archived_at'])->groupBy(fn ($row) => CatalogLabel::key($row->name));

        foreach ($groups as $group) {
            $canonical = $group->sortBy(fn ($row) => [
                $row->archived_at !== null ? 1 : 0,
                $row->code && str_starts_with($row->code, 'DEMO-') ? 1 : 0,
                $row->id,
            ])->first();
            if (! $canonical) continue;

            if (str_starts_with((string) $canonical->code, 'DEMO-')) {
                DB::table('board_types')->where('id', $canonical->id)->update(['name' => CatalogLabel::label($canonical->name)]);
            }

            foreach ($group->where('id', '!=', $canonical->id) as $duplicate) {
                foreach (DB::table('room_board_types')->where('board_type_id', $duplicate->id)->get(['room_id']) as $pivot) {
                    $exists = DB::table('room_board_types')->where(['room_id' => $pivot->room_id, 'board_type_id' => $canonical->id])->exists();
                    if ($exists) {
                        DB::table('room_board_types')->where(['room_id' => $pivot->room_id, 'board_type_id' => $duplicate->id])->delete();
                    } else {
                        DB::table('room_board_types')->where(['room_id' => $pivot->room_id, 'board_type_id' => $duplicate->id])->update(['board_type_id' => $canonical->id]);
                    }
                    $pivots++;
                }
                if ($duplicate->archived_at === null) {
                    DB::table('board_types')->where('id', $duplicate->id)->update(['archived_at' => now()]);
                    $archived++;
                }
            }
        }

        return compact('archived', 'pivots');
    }

    private function normalizeFacilities(): array
    {
        $removed = 0;
        $pivots = 0;
        $groups = DB::table('facilities')->orderBy('id')->get(['id', 'name'])->groupBy(fn ($row) => FacilityLabel::key($row->name));

        foreach ($groups as $group) {
            $canonical = $group->sortBy(fn ($row) => [
                str_starts_with(strtolower($row->name), 'demo ') ? 1 : 0,
                $row->id,
            ])->first();
            if (! $canonical) continue;

            if (str_starts_with(strtolower($canonical->name), 'demo ')) {
                DB::table('facilities')->where('id', $canonical->id)->update(['name' => FacilityLabel::canonicalValue($canonical->name)]);
            }

            foreach ($group->where('id', '!=', $canonical->id) as $duplicate) {
                $isDemo = str_starts_with(strtolower($duplicate->name), 'demo ');
                if (! $isDemo) continue;
                foreach (DB::table('facility_room')->where('facility_id', $duplicate->id)->get(['room_id']) as $pivot) {
                    $exists = DB::table('facility_room')->where(['room_id' => $pivot->room_id, 'facility_id' => $canonical->id])->exists();
                    if ($exists) {
                        DB::table('facility_room')->where(['room_id' => $pivot->room_id, 'facility_id' => $duplicate->id])->delete();
                    } else {
                        DB::table('facility_room')->where(['room_id' => $pivot->room_id, 'facility_id' => $duplicate->id])->update(['facility_id' => $canonical->id]);
                    }
                    $pivots++;
                }
                DB::table('facilities')->where('id', $duplicate->id)->delete();
                $removed++;
            }
        }

        return compact('removed', 'pivots');
    }

    private function normalizeDemoHotelFacilities(): int
    {
        $run = DB::table('demo_seed_runs')->where('name', 'portfolio-v1')->value('summary');
        $ids = collect(json_decode((string) $run, true)['hotel_ids'] ?? [])->filter()->values();
        if ($ids->isEmpty()) return 0;

        $count = 0;
        foreach (DB::table('hotels')->whereIn('id', $ids)->get(['id', 'facilities']) as $hotel) {
            $facilities = collect(json_decode($hotel->facilities ?: '[]', true) ?: [])
                ->map(fn ($facility) => FacilityLabel::canonicalValue($facility))
                ->unique(fn ($facility) => FacilityLabel::key($facility))
                ->values()
                ->all();
            DB::table('hotels')->where('id', $hotel->id)->update(['facilities' => json_encode($facilities)]);
            $count++;
        }

        return $count;
    }
}
