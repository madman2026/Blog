<?php

namespace Modules\Interaction\Providers;

use Modules\Interaction\Interfaces\Repositories\CommentRepository;
use Modules\Interaction\Interfaces\Repositories\EngagementRepository;
use Modules\Interaction\Repositories\EloquentCommentRepository;
use Modules\Interaction\Repositories\EloquentEngagementRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class InteractionServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Interaction';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'interaction';

    /** @var array<int, class-string> */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(CommentRepository::class, EloquentCommentRepository::class);
        $this->app->bind(EngagementRepository::class, EloquentEngagementRepository::class);
    }
}
