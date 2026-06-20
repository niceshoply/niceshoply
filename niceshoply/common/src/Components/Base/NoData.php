<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Components\Base;

use Illuminate\View\Component;

class NoData extends Component
{
    public string $text;

    public string $width;

    public function __construct(?string $text = '', ?string $width = '300')
    {
        $this->text  = $text;
        $this->width = $width;
    }

    public function render()
    {
        return view('common::components.no-data');
    }
}
