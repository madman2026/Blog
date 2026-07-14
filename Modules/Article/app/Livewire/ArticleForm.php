<?php

use Livewire\Form;
use Livewire\Attributes\Validate;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Modules\Article\Models\Article;

class ArticleForm extends Form
{
    public ?Article $article = null;

    public string $title = '';
    public string $summary = '';
    public string $text = '';
    public ?TemporaryUploadedFile $image = null;

    public function fillFromModel(Article $article)
    {
        $this->article = $article;

        $this->fill([
            'title' => $article->title,
            'summary' => $article->summary,
            'text' => $article->text,
        ]);
    }
}