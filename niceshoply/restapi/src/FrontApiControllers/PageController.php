<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\FrontApiControllers;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Page;

class PageController extends BaseController
{
    /**
     * Get page detail by ID.
     *
     * @param  Page  $page
     * @return mixed
     */
    public function show(Page $page): mixed
    {
        try {
            if (! $page->active) {
                throw new Exception('Page not found');
            }

            $page->load(['translation']);
            $page->increment('viewed');

            return read_json_success($page);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Get page detail by slug.
     *
     * @param  Request  $request
     * @param  string  $slug
     * @return mixed
     */
    public function showBySlug(Request $request, string $slug): mixed
    {
        try {
            $page = Page::query()
                ->with(['translation'])
                ->where('slug', $slug)
                ->where('active', true)
                ->firstOrFail();

            $page->increment('viewed');

            return read_json_success($page);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
