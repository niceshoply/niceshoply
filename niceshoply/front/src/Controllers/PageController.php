<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use NiceShoply\Common\Models\Page;
use NiceShoply\Common\Repositories\PageRepo;

class PageController extends Controller
{
    /**
     * Page list (redirects to home)
     *
     * @return RedirectResponse
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('front.home.index');
    }

    /**
     * Show page by ID
     *
     * @param  Page  $page
     * @return mixed
     * @throws Exception
     */
    public function show(Page $page): mixed
    {
        if (! $page->active) {
            abort(404);
        }

        return $this->renderPage($page);
    }

    /**
     * Show page by slug (consistent with product-{slug}, category-{slug}, article-{slug})
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function slugShow(Request $request): mixed
    {
        $slug = $request->slug;
        $page = PageRepo::getInstance()
            ->builder(['slug' => $slug, 'active' => true])
            ->firstOrFail();

        return $this->renderPage($page);
    }

    /**
     * Render page content
     *
     * @param  Page  $page
     * @return mixed
     * @throws Exception
     */
    private function renderPage(Page $page): mixed
    {
        if (! $page->active) {
            abort(404);
        }

        $page->increment('viewed');

        $data = [
            'slug' => $page->slug,
            'page' => $page,
        ];
        $template = $page->translation->template ?? '';
        if ($template) {
            $result         = Blade::render($template, $data);
            $data['result'] = $result;
        }

        return nice_view('pages.show', $data);
    }
}
