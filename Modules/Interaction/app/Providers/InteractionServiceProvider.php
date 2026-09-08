<?php

namespace Modules\Interaction\Providers;

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
}
