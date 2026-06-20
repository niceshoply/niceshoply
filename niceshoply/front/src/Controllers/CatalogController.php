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
use NiceShoply\Common\Models\Catalog;
use NiceShoply\Common\Repositories\ArticleRepo;
use NiceShoply\Common\Repositories\CatalogRepo;

class CatalogController extends Controller
{
    /**
     * @return RedirectResponse
     */
    public function index(): RedirectResponse
    {
        return redirect()->to(route('articles.index'));
    }

    /**
     * @param  Catalog  $catalog
     * @return mixed
     * @throws Exception
     */
    public function show(Catalog $catalog): mixed
    {
        if (! $catalog->active) {
            abort(404);
        }

        $catalogs = CatalogRepo::getInstance()->list(['active' => true]);
        $articles = ArticleRepo::getInstance()->list(['active' => true, 'catalog_id' => $catalog->id]);

        $data = [
            'catalog'  => $catalog,
            'catalogs' => $catalogs,
            'articles' => $articles,
        ];

        return nice_view('catalogs.show', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function slugShow(Request $request): mixed
    {
        $slug    = $request->slug;
        $catalog = CatalogRepo::getInstance()->builder(['active' => true])->where('slug', $slug)->firstOrFail();

        if (! $catalog->active) {
            abort(404);
        }

        $catalogs = CatalogRepo::getInstance()->list(['active' => true]);
        $articles = ArticleRepo::getInstance()->list(['active' => true, 'catalog_id' => $catalog->id]);

        $data = [
            'slug'     => $slug,
            'catalog'  => $catalog,
            'catalogs' => $catalogs,
            'articles' => $articles,
        ];

        return nice_view('catalogs.show', $data);
    }
}
