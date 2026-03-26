<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Deploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute all deployment and optimization commands';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting deployment process...');

        // Liste des commandes à exécuter dans l'ordre
        $commands = [
            'optimize:clear' => 'Clearing all optimized files...',
            'config:clear' => 'Clearing configuration cache...',
            'route:clear' => 'Clearing route cache...',
            'view:clear' => 'Clearing view cache...',
            'cache:clear' => 'Clearing application cache...',
            'migrate' => 'Running database migrations...',
            'db:force-reset-sequences' => 'Force resetting PostgreSQL sequences (Railway version)...',
            'storage:link' => 'Creating storage symbolic links...',
        ];

        foreach ($commands as $command => $description) {
            $this->info($description);

            try {
                $exitCode = Artisan::call($command);

                if ($exitCode === 0) {
                    $this->info("✓ {$command} executed successfully");
                } else {
                    $this->error("✗ {$command} failed with exit code {$exitCode}");
                    Log::error("Deploy command failed: {$command}", ['exit_code' => $exitCode]);
                }

                $this->newLine();
            } catch (\Exception $e) {
                $this->error("✗ {$command} threw exception: " . $e->getMessage());
                Log::error("Deploy command exception: {$command}", ['exception' => $e]);
                $this->newLine();
            }
        }

        $this->info('Deployment process completed!');

        return Command::SUCCESS;
    }
}
