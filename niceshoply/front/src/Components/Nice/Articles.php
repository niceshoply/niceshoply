<?php

namespace NiceShoply\Front\Components\Nice;

use Illuminate\View\Component;
use NiceShoply\Common\Repositories\ArticleRepo;

class Articles extends Component
{
    public $articles;

    public int $limit;

    public int $cols;

    public string $title;

    public function __construct(int $limit = 4, int $cols = 4, string $title = '')
    {
        $this->limit    = $limit;
        $this->cols     = $cols;
        $this->title    = $title ?: trans('front/home.news_blog');
        $this->articles = ArticleRepo::getInstance()->getLatestArticles($limit);
    }

    public function render(): mixed
    {
        if ($this->articles->isEmpty()) {
            return '';
        }

        return view('components.nice.articles');
    }
}
