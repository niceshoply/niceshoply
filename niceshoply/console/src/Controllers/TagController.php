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
use NiceShoply\Common\Models\Tag;
use NiceShoply\Common\Repositories\TagRepo;
use NiceShoply\Console\Requests\TagRequest;

class TagController extends BaseController
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
            'criteria' => TagRepo::getCriteria(),
            'tags'     => TagRepo::getInstance()->list($filters),
        ];

        return nice_view('console::tags.index', $data);
    }

    /**
     * Tag creation tag.
     *
     * @return mixed
     */
    public function create(): mixed
    {
        $data = [
            'tag' => new Tag,
        ];

        return nice_view('console::tags.form', $data);
    }

    /**
     * @param  TagRequest  $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function store(TagRequest $request): RedirectResponse
    {
        try {
            $data = $request->all();
            TagRepo::getInstance()->create($data);

            return back()->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Tag  $tag
     * @return mixed
     */
    public function edit(Tag $tag): mixed
    {
        $data = [
            'tag' => $tag,
        ];

        return nice_view('console::tags.form', $data);
    }

    /**
     * @param  TagRequest  $request
     * @param  Tag  $tag
     * @return RedirectResponse
     */
    public function update(TagRequest $request, Tag $tag): RedirectResponse
    {
        try {
            $data = $request->all();
            TagRepo::getInstance()->update($tag, $data);

            return back()->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Tag  $tag
     * @return RedirectResponse
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        try {
            TagRepo::getInstance()->destroy($tag);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
