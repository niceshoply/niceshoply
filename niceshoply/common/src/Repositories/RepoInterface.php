<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

interface RepoInterface
{
    public function list(array $filters = []);

    public function all(array $filters = []);

    public function detail(int $id);

    public function create($data);

    public function update(mixed $item, $data);

    public function destroy(mixed $item);

    public function builder(array $filters = []);
}
