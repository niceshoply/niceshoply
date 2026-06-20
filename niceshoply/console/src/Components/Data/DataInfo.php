<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Components\Data;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\Component;

class DataInfo extends Component
{
    /**
     * The paginator instance
     *
     * @var LengthAwarePaginator
     */
    public ?LengthAwarePaginator $paginator;

    /**
     * Create a new component instance.
     *
     * @param  LengthAwarePaginator|null  $paginator
     */
    public function __construct(?LengthAwarePaginator $paginator = null)
    {
        $this->paginator = $paginator;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('console::components.data.data-info');
    }
}
