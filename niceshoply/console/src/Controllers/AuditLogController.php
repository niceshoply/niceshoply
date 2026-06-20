<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Illuminate\Http\Request;
use NiceShoply\Common\Models\AuditActivity;

class AuditLogController extends BaseController
{
    public function index(Request $request): mixed
    {
        $query = AuditActivity::query()
            ->with(['causer', 'subject'])
            ->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->get('log_name'));
        }
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->get('causer_id'));
        }
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->get('subject_type'));
        }

        $data = [
            'activities' => $query->paginate(20),
        ];

        return nice_view('console::audit_logs.index', $data);
    }
}
