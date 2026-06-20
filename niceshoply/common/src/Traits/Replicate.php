<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Traits;

trait Replicate
{
    public function deepReplicate(?array $except = null)
    {
        $copy = parent::replicate($except);
        $copy->push();

        foreach ($this->getRelations() as $relation => $entries) {
            foreach ($entries as $entry) {
                $newEntry = $entry->replicate();
                if ($newEntry->push()) {
                    $copy->{$relation}()->save($copy);
                }
            }
        }

        return $copy;
    }
}
