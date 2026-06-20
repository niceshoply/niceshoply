<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Article;
use NiceShoply\Common\Repositories\ArticleRepo;
use NiceShoply\Common\Repositories\CatalogRepo;
use NiceShoply\Common\Repositories\TagRepo;
use NiceShoply\Common\Resources\CatalogSimple;
use NiceShoply\Console\Requests\ArticleRequest;
use NiceShoply\Console\Resources\ArticleNameResource;
use NiceShoply\Console\Resources\ProductNameResource;
use Throwable;

class ArticleController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria' => ArticleRepo::getCriteria(),
            'articles' => ArticleRepo::getInstance()->list($filters),
        ];

        return nice_view('console::articles.index', $data);
    }

    /**
     * Article creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Article);
    }

    /**
     * @param  ArticleRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(ArticleRequest $request): RedirectResponse
    {
        try {
            $data    = $request->all();
            $article = ArticleRepo::getInstance()->create($data);

            return redirect(console_route('articles.index'))
                ->with('instance', $article)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Article  $article
     * @return mixed
     * @throws Exception
     */
    public function edit(Article $article): mixed
    {
        return $this->form($article);
    }

    /**
     * @param  $article
     * @return mixed
     * @throws Exception
     */
    public function form($article): mixed
    {
        // Preload related article relationships
        if ($article->id) {
            $article->load([
                'relatedArticles.relatedArticle.translation',
                'products.translation',
                'tags.translation',
            ]);
        }

        $selectedRelatedArticles = [];
        if ($article->id && $article->relatedArticles) {
            $relatedArticles         = $article->relatedArticles->pluck('relatedArticle')->filter();
            $selectedRelatedArticles = ArticleNameResource::collection(
                $relatedArticles
            )->toArray(request());
        }

        $selectedRelatedProducts = [];
        if ($article->id && $article->products) {
            $selectedRelatedProducts = ProductNameResource::collection(
                $article->products
            )->toArray(request());
        }

        $selectedTags = [];
        if ($article->id && $article->tags) {
            $selectedTags = $article->tags->map(function ($tag) {
                return [
                    'id'   => $tag->id,
                    'name' => $tag->translation->name ?? $tag->slug,
                ];
            })->toArray();
        }

        $catalogs = CatalogSimple::collection(CatalogRepo::getInstance()->all(['active' => 1]))->jsonSerialize();

        $tags = TagRepo::getInstance()->all(['active' => 1])->map(function ($tag) {
            return [
                'id'   => $tag->id,
                'name' => $tag->translation->name ?? $tag->slug,
            ];
        })->toArray();

        $data = [
            'article'                 => $article,
            'catalogs'                => $catalogs,
            'tags'                    => $tags,
            'selectedRelatedArticles' => $selectedRelatedArticles,
            'selectedRelatedProducts' => $selectedRelatedProducts,
            'selectedTags'            => $selectedTags,
        ];

        return nice_view('console::articles.form', $data);
    }

    /**
     * @param  ArticleRequest  $request
     * @param  Article  $article
     * @return RedirectResponse
     */
    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        try {
            $data = $request->all();
            ArticleRepo::getInstance()->update($article, $data);

            return redirect(console_route('articles.index'))
                ->with('instance', $article)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Article  $article
     * @return RedirectResponse
     */
    public function destroy(Article $article): RedirectResponse
    {
        try {
            ArticleRepo::getInstance()->destroy($article);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
