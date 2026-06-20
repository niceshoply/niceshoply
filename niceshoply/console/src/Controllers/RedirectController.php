<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Redirect;
use NiceShoply\Common\Repositories\RedirectRepo;

/**
 * SEO URL 重定向后台管理。
 */
class RedirectController extends BaseController
{
    public function index(Request $request): mixed
    {
        $data = [
            'criteria'  => RedirectRepo::getCriteria(),
            'redirects' => RedirectRepo::getInstance()->list($request->all()),
        ];

        return nice_view('console::redirects.index', $data);
    }

    public function create(): mixed
    {
        return nice_view('console::redirects.form', ['redirect' => new Redirect]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            RedirectRepo::getInstance()->create($this->normalize($request));

            return redirect(console_route('redirects.index'))->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('redirects.create'))->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(Redirect $redirect): mixed
    {
        return nice_view('console::redirects.form', ['redirect' => $redirect]);
    }

    public function update(Request $request, Redirect $redirect): RedirectResponse
    {
        try {
            RedirectRepo::getInstance()->update($redirect, $this->normalize($request));

            return redirect(console_route('redirects.index'))->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('redirects.edit', [$redirect->id]))->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        try {
            RedirectRepo::getInstance()->destroy($redirect);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(Request $request): array
    {
        $status = (int) $request->input('status_code', 301);

        return [
            'source_path' => $request->input('source_path', ''),
            'target_path' => $request->input('target_path', ''),
            'status_code' => in_array($status, [301, 302], true) ? $status : 301,
            'active'      => (bool) $request->input('active', true),
        ];
    }
}
