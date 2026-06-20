<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Components\Forms;

use Illuminate\View\Component;

class Codemirror extends Component
{
    public string $name;

    public string $value;

    public function __construct(string $name, ?string $value)
    {
        $this->name  = $name;
        $this->value = html_entity_decode($value, ENT_QUOTES);
    }

    public function render()
    {
        return view('console::components.form.codemirror');
    }
}
