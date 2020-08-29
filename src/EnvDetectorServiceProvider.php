<?php

/**
 * Purpose:
 *  Service provider for laravel. Starts the setup process of the environment detector.
 * History:
 *  100919 - Wilkes: Created file
 *  020620 - Wilkes: Adjusted comments and use statements.
 * @author Casper Wilkes <casper@casperwilkes.net>
 * @package CasperWilkes\EnvDetector
 * @copyright 2019 - casper wilkes
 * @license MIT
 */

namespace EnvDetector;

use EnvDetector\Commands\{Publish, UnPublish};
use Illuminate\Support\ServiceProvider;

/**
 * Class EnvDetectorServiceProvider
 * @package EnvDetector
 */
class EnvDetectorServiceProvider extends ServiceProvider {

    /**
     * Bootup functionality of service provider
     * @return void
     */
    public function boot(): void {
        // Publish the environment detector and configs //
        $this->publishes(
            [
                __DIR__ . '/Stubs/config/environment_detector.stub' => config_path('environment_detector.php'),
                __DIR__ . '/Stubs/environment_detector.stub' => base_path('bootstrap/environment_detector.php'),
            ]
            , 'env-detector');

    }

    /**
     * Register functionality of service provider
     * @return void
     */
    public function register(): void {
        $this->commands([
                            Publish::class,
                            UnPublish::class,
                        ]);
    }
}
