<?php


namespace JustBetter\InstagramFeed;


use JustBetter\InstagramFeed\Commands\CreateBasicProfile;
use JustBetter\InstagramFeed\Commands\RefreshAuthorizedFeeds;
use JustBetter\InstagramFeed\Commands\RefreshTokens;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class InstagramFeedServiceProvider extends ServiceProvider
{

    public function boot(Filesystem $filesystem)
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateBasicProfile::class,
                RefreshAuthorizedFeeds::class,
                RefreshTokens::class,
            ]);
        }

        if (!class_exists('CreateInstagramFeedTokenTable') && !$this->migrationAlreadyPublished($filesystem,
                '_create_instagram_feed_token_table.php')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_instagram_feed_token_table.php.stub' => App::databasePath('migrations/'.date('Y_m_d_His',
                        time()).'_create_instagram_feed_token_table.php'),
            ], 'migrations');
        }

        if (!class_exists('CreateInstagramBasicProfileTable') && !$this->migrationAlreadyPublished($filesystem,
                '_create_instagram_basic_profile_table.php')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_instagram_basic_profile_table.php.stub' => App::databasePath('migrations/'.date('Y_m_d_His',
                        time()).'_create_instagram_basic_profile_table.php'),
            ], 'migrations');
        }

        if (Instagram::$registersRoutes) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }

        $this->loadViewsFrom(__DIR__.'/../views', 'instagram-feed');

        $this->publishes([
            __DIR__.'/../config/instagram-feed.php' => App::configPath('instagram-feed.php')
        ]);
    }

    /**
     * @param  Filesystem  $filesystem
     * @param $filename
     * @return bool
     */
    protected function migrationAlreadyPublished(Filesystem $filesystem, $filename): bool
    {
        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
                ->flatMap(function ($path) use ($filesystem, $filename) {
                    return $filesystem->glob($path.'*'.$filename);
                })
                ->count() > 0;
    }

    public function register()
    {
        $this->app->bind(Instagram::class, function () {
            return new Instagram(Config::get('instagram-feed'));
        });
    }
}
