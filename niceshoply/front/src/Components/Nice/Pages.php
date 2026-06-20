<?php

namespace NiceShoply\Front\Components\Nice;

use Illuminate\View\Component;
use NiceShoply\Common\Repositories\PageRepo;

class Pages extends Component
{
    public $pages;

    public int $limit;

    public int $cols;

    public string $title;

    public function __construct(int $limit = 4, int $cols = 4, string $title = '')
    {
        $this->limit = $limit;
        $this->cols  = $cols;
        $this->title = $title ?: trans('front/home.more_to_explore', [], null) ?: 'More to Explore';

        $this->pages = PageRepo::getInstance()
            ->builder(['active' => true])
            ->orderBy('position')
            ->limit($limit)
            ->get();
    }

    public function render(): mixed
    {
        if ($this->pages->isEmpty()) {
            return '';
        }

        return view('components.nice.pages');
    }
}
