<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Enums\PostType;
use Modules\Interaction\Enums\CommentStatus;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserRole;
use Modules\User\Enums\UserStatus;

function translationDirectories(): array
{
    return [
        lang_path(),
        ...collect(File::directories(base_path('Modules')))
            ->map(fn (string $moduleDirectory): string => $moduleDirectory.DIRECTORY_SEPARATOR.'lang')
            ->filter(fn (string $translationDirectory): bool => File::isDirectory($translationDirectory))
            ->all(),
    ];
}

function placeholders(string $translation): array
{
    preg_match_all('/:([\pL_][\pL\pN_]*)/u', $translation, $matches);

    return collect($matches[1])->map(Str::lower(...))->sort()->values()->all();
}

test('English and Persian JSON catalogs have matching complete keys', function (): void {
    foreach (translationDirectories() as $translationDirectory) {
        $englishPath = $translationDirectory.DIRECTORY_SEPARATOR.'en.json';
        $persianPath = $translationDirectory.DIRECTORY_SEPARATOR.'fa.json';

        if (! File::exists($englishPath) && ! File::exists($persianPath)) {
            continue;
        }

        expect(File::exists($englishPath))->toBeTrue()
            ->and(File::exists($persianPath))->toBeTrue();

        $english = json_decode(File::get($englishPath), true, flags: JSON_THROW_ON_ERROR);
        $persian = json_decode(File::get($persianPath), true, flags: JSON_THROW_ON_ERROR);

        expect(array_keys($english))->toEqualCanonicalizing(array_keys($persian));

        foreach ($english as $key => $translation) {
            expect($translation)->not->toBe('')
                ->and($persian[$key])->not->toBe('')
                ->and(placeholders($translation))->toBe(placeholders($persian[$key]));
        }
    }
});

test('English and Persian namespaced catalogs have matching keys', function (): void {
    foreach (translationDirectories() as $translationDirectory) {
        $englishDirectory = $translationDirectory.DIRECTORY_SEPARATOR.'en';
        $persianDirectory = $translationDirectory.DIRECTORY_SEPARATOR.'fa';

        if (! File::isDirectory($englishDirectory) && ! File::isDirectory($persianDirectory)) {
            continue;
        }

        $englishFiles = collect(File::files($englishDirectory))->keyBy(fn ($file): string => $file->getFilename());
        $persianFiles = collect(File::files($persianDirectory))->keyBy(fn ($file): string => $file->getFilename());

        expect($englishFiles->keys()->all())->toEqualCanonicalizing($persianFiles->keys()->all());

        foreach ($englishFiles as $filename => $englishFile) {
            $english = Arr::dot(require $englishFile->getPathname());
            $persian = Arr::dot(require $persianFiles[$filename]->getPathname());

            expect(array_keys($english))->toEqualCanonicalizing(array_keys($persian));

            foreach ($english as $key => $translation) {
                expect($translation)->not->toBe('')
                    ->and($persian[$key])->not->toBe('')
                    ->and(placeholders($translation))->toBe(placeholders($persian[$key]));
            }
        }
    }
});

test('every literal interface translation is present in both JSON catalogs', function (): void {
    $sourceDirectories = [
        app_path(),
        base_path('Modules'),
        resource_path(),
        base_path('routes'),
    ];

    $usedKeys = collect($sourceDirectories)
        ->flatMap(fn (string $directory): array => File::allFiles($directory))
        ->filter(fn ($file): bool => $file->getExtension() === 'php')
        ->flatMap(function ($file): array {
            preg_match_all('/(?:__|trans)\(\s*([\'\"])(.*?)\1/s', File::get($file->getPathname()), $matches);

            return $matches[2];
        })
        ->reject(fn (string $key): bool => str_contains($key, '::') || preg_match('/^[a-z]+\./', $key) === 1)
        ->merge([
            ...array_column(PostStatus::cases(), 'value'),
            ...array_column(PostType::cases(), 'value'),
            ...array_column(CommentStatus::cases(), 'value'),
            ...array_column(AuthorApplicationStatus::cases(), 'value'),
            ...array_column(UserRole::cases(), 'value'),
            ...array_column(UserStatus::cases(), 'value'),
            'Notification',
        ])
        ->unique()
        ->sort()
        ->values();

    $english = json_decode(File::get(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);
    $persian = json_decode(File::get(lang_path('fa.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($usedKeys->diff(array_keys($english))->values()->all())->toBeEmpty()
        ->and($usedKeys->diff(array_keys($persian))->values()->all())->toBeEmpty();
});

test('every literal namespaced translation resolves in both locales', function (): void {
    $sourceDirectories = [
        app_path(),
        base_path('Modules'),
        resource_path(),
        base_path('routes'),
    ];

    $usedKeys = collect($sourceDirectories)
        ->flatMap(fn (string $directory): array => File::allFiles($directory))
        ->filter(fn ($file): bool => $file->getExtension() === 'php')
        ->flatMap(function ($file): array {
            preg_match_all('/(?:__|trans)\(\s*([\'\"])(.*?)\1/s', File::get($file->getPathname()), $matches);

            return $matches[2];
        })
        ->filter(fn (string $key): bool => str_contains($key, '::') || preg_match('/^[a-z]+\./', $key) === 1)
        ->unique()
        ->sort()
        ->values();

    foreach (['en', 'fa'] as $locale) {
        app()->setLocale($locale);

        foreach ($usedKeys as $key) {
            expect(__($key))->not->toBe($key);
        }
    }
});

test('the interface renders with the correct language and direction', function (string $locale, string $direction, string $heading): void {
    $this->withSession(['locale' => $locale])
        ->get(route('auth.login'))
        ->assertSuccessful()
        ->assertSee('lang="'.$locale.'"', false)
        ->assertSee('dir="'.$direction.'"', false)
        ->assertSee($heading);
})->with([
    'English' => ['en', 'ltr', 'Welcome back'],
    'Persian' => ['fa', 'rtl', 'خوش آمدید'],
]);
