<?php

namespace Modules\Blog\Actions;

use Modules\Blog\Models\Post;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class SaveFeaturedImage
{
    public function handle(Post $post, ?UploadedFile $image, ?string $alt): Post
    {
        if ($image) {
            $post
                ->addMedia($image)
                ->usingFileName($image->getClientOriginalName())
                ->withCustomProperties(['alt' => $alt])
                ->withResponsiveImages()
                ->toMediaCollection('featured_image');
        } elseif ($post->hasMedia('featured_image')) {
            $media = $post->getFirstMedia('featured_image');
            $media?->setCustomProperty('alt', $alt);
            $media?->save();
        }

        return $post->load('media');
    }
}
