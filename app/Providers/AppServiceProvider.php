<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Blog\Models\Post;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            HtmlSanitizerInterface::class,
            fn (): HtmlSanitizer => new HtmlSanitizer(
                (new HtmlSanitizerConfig)
                    ->allowSafeElements()
                    ->allowRelativeLinks()
                    ->allowRelativeMedias()
                    ->allowLinkSchemes(['http', 'https', 'mailto'])
                    ->allowMediaSchemes(['http', 'https'])
                    ->withMaxInputLength(250_000),
            ),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Relation::enforceMorphMap([
            'post' => Post::class,
            'user' => User::class,
        ]);

        Gate::before(
            fn (User $user): ?bool => $user->hasRole(UserRole::SuperUser->value) ? true : null,
        );
    }
}
