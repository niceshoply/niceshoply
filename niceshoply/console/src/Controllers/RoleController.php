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
use NiceShoply\Common\Repositories\RoleRepo;
use NiceShoply\Console\Repositories\RouteRepo;
use Spatie\Permission\Models\Role;

class RoleController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $data = [
            'roles' => Role::query()->paginate(),
        ];

        return nice_view('console::roles.index', $data);
    }

    /**
     * @param  Role  $role
     * @return Role
     */
    public function show(Role $role): Role
    {
        return $role;
    }

    /**
     * Role creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Role);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        try {
            $data = $request->all();
            $role = RoleRepo::getInstance()->create($data);

            return redirect(console_route('roles.index'))
                ->with('instance', $role)
                ->with('success', console_trans('common.saved_success'));
        } catch (Exception $e) {
            return redirect(console_route('roles.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Role  $role
     * @return mixed
     * @throws Exception
     */
    public function edit(Role $role): mixed
    {
        return $this->form($role);
    }

    /**
     * @param  Role  $role
     * @return mixed
     */
    public function form(Role $role): mixed
    {
        $data = [
            'role'        => $role,
            'permissions' => RouteRepo::getInstance($role)->getConsolePermissions(),
        ];

        return nice_view('console::roles.form', $data);
    }

    /**
     * @param  Request  $request
     * @param  Role  $role
     * @return mixed
     */
    public function update(Request $request, Role $role): mixed
    {
        try {
            $data = $request->all();
            RoleRepo::getInstance()->update($role, $data);

            return redirect(console_route('roles.index'))
                ->with('instance', $role)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('roles.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Role  $role
     * @return RedirectResponse
     */
    public function destroy(Role $role): RedirectResponse
    {
        try {
            RoleRepo::getInstance()->destroy($role);

            return redirect(console_route('roles.index'))
                ->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(console_route('roles.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
