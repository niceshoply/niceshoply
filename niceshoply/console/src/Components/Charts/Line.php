<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Components\Charts;

use Illuminate\View\Component;

class Line extends Component
{
    public string $id;

    public string $title;

    public array $labels;

    public array $items;

    /**
     * @param  string  $id
     * @param  string  $title
     * @param  array  $labels
     * @param  array  $data
     */
    public function __construct(string $id, string $title, array $labels, array $data)
    {
        $this->id     = $id;
        $this->title  = $title;
        $this->labels = $labels;
        $this->items  = $data;
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return view('console::components.charts.line');
    }
}
