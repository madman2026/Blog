<?php

namespace Modules\User\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\User\Interfaces\Repositories\AuthorApplicationRepository;
use Modules\User\Interfaces\Repositories\UserRepository;
use Modules\User\Repositories\EloquentAuthorApplicationRepository;
use Modules\User\Repositories\EloquentUserRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class UserServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'User';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'user';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(AuthorApplicationRepository::class, EloquentAuthorApplicationRepository::class);
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
