<?php

namespace NiceShoply\Front\Components\Nice;

use Illuminate\View\Component;
use NiceShoply\Front\Repositories\HomeRepo;

class Slideshow extends Component
{
    public array $slides;

    public function __construct()
    {
        $this->slides = HomeRepo::getInstance()->getSlideShow();
    }

    public function render(): mixed
    {
        if (empty($this->slides)) {
            return '';
        }

        return view('components.nice.slideshow');
    }
}
