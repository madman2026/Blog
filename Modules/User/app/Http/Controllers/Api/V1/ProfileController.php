<?php

namespace Modules\User\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\User\Http\Requests\Api\V1\UpdateProfileRequest;
use Modules\User\Models\Skill;
use Modules\User\Transformers\UserResource;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->load(['skills', 'media']));
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $validated = $request->validated();
        $user = $request->user();

        DB::transaction(function () use ($validated, $user): void {
            $user->update(collect($validated)->except('skills')->all());

            if (array_key_exists('skills', $validated)) {
                $skillIds = collect($validated['skills'])
                    ->map(fn (string $name): string => Str::squish($name))
                    ->filter()
                    ->unique(fn (string $name): string => mb_strtolower($name))
                    ->map(function (string $name): int {
                        $slug = Str::slug($name) ?: 'skill-'.hash('xxh3', mb_strtolower($name));

                        return Skill::query()->firstOrCreate(
                            ['slug' => $slug],
                            ['name' => $name],
                        )->getKey();
                    })
                    ->all();

                $user->skills()->sync($skillIds);
            }
        });

        return new UserResource($user->refresh()->load(['skills', 'media']));
    }
}
