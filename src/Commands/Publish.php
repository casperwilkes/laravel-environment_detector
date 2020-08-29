<?php

/**
 * Purpose:
 *  Console commands to finish env detector setup
 * History:
 *  100919 - Wilkes: Created file
 * @author Casper Wilkes <casper@casperwilkes.net>
 * @package CasperWilkes\EnvDetector
 * @copyright 2019 - casper wilkes
 * @license MIT
 */

namespace EnvDetector\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Class Publish
 * @package EnvDetector\Console
 */
class Publish extends Command {

    /**
     * Describes the vendor publish command
     * @var string
     */
    private $publish = '!!Must have run `php artisan vendor:publish --tag=env-detector`!!';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'envdetector:publish '
                           . '{--a|all : Writes all [Default]}'
                           . '{--b|bootstrap : Create bootstrap files}'
                           . '{--c|configs : (Over)Writes the .env config files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates the environment detector bootstrap and config files.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->info('Started environment detector setup');

        // Get our options from top of options array //
        $opts = array_slice($this->options(), 0, 3);

        // Check if all are false, or all is true //
        $all = !in_array(true, $opts, true) || $this->option('all');

        // Set remaining options //
        $config = $all ? : $this->option('configs');
        $bootstrap = $all ? : $this->option('bootstrap');

        if ($config) {
            $this->comment('Publishing configs');
            $this->configs();
            $this->comment('Finished publishing configs');
        }

        if ($bootstrap) {
            $this->comment('Bootstrapping App.php');
            $this->bootstrap();
            $this->comment('Finished bootstrapping App.php');
        }

        $this->info('Finished environment detector setup');
    }

    /**
     * Creates config files
     * @return void
     */
    private function configs(): void {
        // Config necessary to setup //
        $files = collect(config('environment_detector.environments'));

        // Config file must exist //
        if ($files->isEmpty()) {
            $this->warn('Cannot create configs for environment');
            $this->warn($this->publish);

            return;
        }

        // Overwrite any existing configs //
        $overwrite_all = false;

        // Check if base configs exist //
        $exists = $files->map(function ($host, $env) {
            $file_name = ".env.{$env}";

            return File::exists(base_path($file_name));
        });

        // Check if any exist //
        $config_exist = in_array(true, $exists->toArray(), true);

        // No configs exist, create configs //
        if (!$config_exist) {
            $overwrite_all = true;
        } elseif ($this->confirm('Would you like to overwrite all?')) {
            // User would like to overwrite existing //
            $overwrite_all = true;
        }

        // Loop over collection and build files //
        $files->each(function ($host, $env) use ($overwrite_all) {
            // Shortname of env //
            $file_name = ".env.{$env}";

            // If overwrite all is true, just write, if false, ask for each //
            if ($overwrite_all || $this->confirm("Overwrite: `{$file_name}` ?")) {
                // Copy the files //
                if (File::copy(base_path('.env'), base_path($file_name))) {
                    $this->comment("Created: {$file_name}");
                } else {
                    $this->warn("Could not create: {$file_name}");
                }
            }
        });
    }

    /**
     * Backs up, and writes to app.php. This is how the environment detector "registers"
     * @return void
     */
    private function bootstrap(): void {

        if (!File::exists(base_path('bootstrap/environment_detector.php'))) {
            $this->warn('cannot perform bootstrap of App.php');
            $this->warn($this->publish);

            return;
        }

        // Full path to app.php //
        $file_path = base_path('bootstrap/app.php');

        // Check for backed up app files //
        $backups = File::glob($file_path . '.*');

        if (!empty($backups)) {
            $this->warn('App.php is already backed up.');

            // If backed up, check if we should continue bootstrapping anyway //
            if (!$this->confirm(' Would you like to bootstrap anyway?')) {
                $this->comment('App.php unchanged');

                return;
            }
        }

        try {
            // Check if the file actually exists //
            if (!File::exists($file_path)) {
                $this->warn("{$file_path} does not exist");
            } elseif (!File::isReadable($file_path)) {
                $this->warn("{$file_path} cannot be read");
            } elseif (!File::isWritable($file_path)) {
                $this->warn("{$file_path} is not writable");
            } else {
                // Read in app as an array //
                $file = file($file_path);
                // Key of last return index //
                $needle = false;

                // Account for zero index //
                $count = count($file) - 1;

                // Get last 5 lines //
                for ($i = $count; $i >= ($count - 5); --$i) {
                    // Check for return statement //
                    if (false !== stripos($file[$i], 'return')) {
                        // Get key of last return //
                        $needle = $i;
                    }

                    // Break if key is found //
                    if (is_int($needle)) {
                        break;
                    }
                }

                if (!is_int($needle)) {
                    $this->warn('Could not find return statement in app.php');
                } else {
                    // Get contents of stub as array //
                    $require = file(dirname(__DIR__) . '/Stubs/require.stub');

                    // Add contents to file //
                    array_splice($file, $needle, 0, $require);

                    // Backup app.php path //
                    $backup = $file_path . '._bu_' . date('Ymd');

                    // backup original file //
                    if (!File::copy($file_path, $backup)) {
                        $this->error('Could not backup app.php');
                    } else {
                        $this->comment('App.php Backed up: ' . basename($backup));

                        // Attempt to write to app.php //
                        if (!File::put($file_path, $file)) {
                            // Put contents of file array //
                            $this->error('Could not bootstrap app.php');
                        } else {
                            $this->comment('App.php bootstrapped');
                        }
                    }
                }
            }

        } catch (Exception $e) {
            $this->alert($e->getMessage());
            Log::critical(__METHOD__, ['Exception' => $e]);
            $this->error('An exception has occurred while creating file. Please check logs for more information');
        }
    }

}
