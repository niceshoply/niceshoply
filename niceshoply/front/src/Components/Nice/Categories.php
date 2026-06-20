<?php

namespace NiceShoply\Front\Components\Nice;

use Illuminate\View\Component;
use NiceShoply\Common\Repositories\CategoryRepo;

class Categories extends Component
{
    public $categories;

    public int $limit;

    public int $cols;

    public int $parent;

    public function __construct(int $limit = 8, int $cols = 4, int $parent = 0)
    {
        $this->limit  = $limit;
        $this->cols   = $cols;
        $this->parent = $parent;

        $this->categories = CategoryRepo::getInstance()
            ->builder(['active' => true, 'parent_id' => $parent])
            ->with(['translation'])
            ->orderBy('position')
            ->limit($limit)
            ->get();
    }

    public function render(): mixed
    {
        if ($this->categories->isEmpty()) {
            return '';
        }

        return view('components.nice.categories');
    }
}
