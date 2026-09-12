<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

class DemoSeed extends Command
{
    protected $signature = 'demo:seed {--date= : Anchor date YYYY-MM-DD; defaults to today}';
    protected $description = 'Create the local fictional portfolio dataset once, without deleting existing data';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Demo seeding is restricted to local/testing.');
            return self::FAILURE;
        }
        $date = $this->option('date') ?: now()->toDateString();
        validator(['date' => $date], ['date' => ['required', 'date_format:Y-m-d']])->validate();
        $summary = app(DemoSeeder::class)->seed($date);
        $this->call('demo:images');
        $this->line(json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('Existing demo runs are left unchanged. Logins: supplier01@demo.bookyourhotel.test, user@demo.bookyourhotel.test, admin@demo.bookyourhotel.test, superadmin@demo.bookyourhotel.test; password: Demo-Local-2026!');
        return self::SUCCESS;
    }
}
