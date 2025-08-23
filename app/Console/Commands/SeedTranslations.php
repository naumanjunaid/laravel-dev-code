<?php

namespace App\Console\Commands;

use Database\Seeders\TranslationSeeder;
use Illuminate\Console\Command;

class SeedTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:translations {--total=1000} {--chunk=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed translations with given total and chunk size';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $total = (int) $this->option('total');
        $chunk = (int) $this->option('chunk');

        $this->info("Seeding {$total} translations in chunks of {$chunk}...");

        $seeder = new TranslationSeeder;
        $seeder->run($total, $chunk);

        $this->info('Seeder completed.');
    }
}
