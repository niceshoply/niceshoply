<?php

namespace NiceShoply\Front\Components\Nice;

use Illuminate\View\Component;
use NiceShoply\Common\Repositories\ProductRepo;

class Products extends Component
{
    public $products;

    public string $type;

    public int $limit;

    public int $cols;

    public string $title;

    public function __construct(
        string $type = 'latest',
        int $limit = 8,
        int $cols = 4,
        string $title = ''
    ) {
        $this->type  = $type;
        $this->limit = $limit;
        $this->cols  = $cols;
        $this->title = $title ?: $this->getDefaultTitle();

        $this->products = match ($type) {
            'bestseller' => ProductRepo::getInstance()->getBestSellerProducts($limit),
            default      => ProductRepo::getInstance()->getLatestProducts($limit),
        };
    }

    private function getDefaultTitle(): string
    {
        return match ($this->type) {
            'bestseller' => trans('front/home.bestseller'),
            default      => trans('front/home.new_arrival'),
        };
    }

    public function render(): mixed
    {
        if ($this->products->isEmpty()) {
            return '';
        }

        return view('components.nice.products');
    }
}
