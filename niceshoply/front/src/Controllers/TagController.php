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
use NiceShoply\Common\Models\Tag;
use NiceShoply\Common\Repositories\ArticleRepo;
use NiceShoply\Common\Repositories\TagRepo;

class TagController extends Controller
{
    /**
     * @return RedirectResponse
     * @throws Exception
     */
    public function index(): RedirectResponse
    {
        return redirect()->to(front_route('articles.index'));
    }

    /**
     * @param  Tag  $tag
     * @return mixed
     * @throws Exception
     */
    public function show(Tag $tag): mixed
    {
        if (! $tag->active) {
            abort(404);
        }

        $tags     = TagRepo::getInstance()->list(['active' => true]);
        $articles = ArticleRepo::getInstance()->list(['active' => true, 'tag_id' => $tag->id]);

        $data = [
            'slug'     => $tag->slug,
            'tag'      => $tag,
            'tags'     => $tags,
            'articles' => $articles,
        ];

        return nice_view('tags.show', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function slugShow(Request $request): mixed
    {
        $slug = $request->slug;
        $tag  = TagRepo::getInstance()->builder(['active' => true])->where('slug', $slug)->firstOrFail();

        if (! $tag->active) {
            abort(404);
        }

        $tags     = TagRepo::getInstance()->list(['active' => true]);
        $articles = ArticleRepo::getInstance()->list(['active' => true, 'tag_id' => $tag->id]);

        $data = [
            'slug'     => $slug,
            'tag'      => $tag,
            'tags'     => $tags,
            'articles' => $articles,
        ];

        return nice_view('tags.show', $data);
    }
}
