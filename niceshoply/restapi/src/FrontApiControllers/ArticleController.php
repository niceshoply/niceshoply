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
use NiceShoply\Common\Models\Article;
use NiceShoply\Common\Repositories\ArticleRepo;
use NiceShoply\Common\Resources\ArticleListItem;

class ArticleController extends BaseController
{
    /**
     * Get article list (only active articles).
     *
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        try {
            $filters           = $request->all();
            $filters['active'] = true;
            $articles          = ArticleRepo::getInstance()->list($filters);

            return ArticleListItem::collection($articles);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Get article detail.
     *
     * @param  Article  $article
     * @return mixed
     */
    public function show(Article $article): mixed
    {
        try {
            if (! $article->active) {
                throw new Exception('Article not found');
            }

            $article->load([
                'translation',
                'catalog.translation',
                'tags.translation',
            ]);

            // Increment view count
            $article->increment('viewed');

            // Get related articles and products
            $repo            = ArticleRepo::getInstance();
            $relatedArticles = $repo->getRelatedArticles($article);
            $relatedProducts = $repo->getRelatedProducts($article);

            $data                     = $article->toArray();
            $data['related_articles'] = ArticleListItem::collection($relatedArticles);
            $data['related_products'] = $relatedProducts;

            return read_json_success($data);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
