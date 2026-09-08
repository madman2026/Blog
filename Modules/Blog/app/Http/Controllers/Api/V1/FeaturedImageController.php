<?php

namespace Modules\Blog\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Blog\Http\Requests\Api\V1\UploadFeaturedImageRequest;
use Modules\Blog\Models\Post;
use Modules\Blog\Transformers\PostResource;

class FeaturedImageController extends Controller
{
    public function __invoke(UploadFeaturedImageRequest $request, Post $managedPost): PostResource
    {
        $managedPost
            ->addMediaFromRequest('image')
            ->withCustomProperties(['alt' => $request->validated('alt')])
            ->withResponsiveImages()
            ->toMediaCollection('featured_image');

        return new PostResource($managedPost->load([
            'author.skills',
            'author.media',
            'translations',
            'categories.translations',
            'tags.translations',
            'media',
        ])->loadCount(['comments', 'likes', 'views']));
    }
}
