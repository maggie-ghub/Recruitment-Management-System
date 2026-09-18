<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * FR-ADM-004: Audit logs shall be accessible only to Administrators and HR Managers.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['Admin', 'HR Manager'])) {
            return response()->json(['message' => 'Unauthorized. Access restricted to Admin and HR Manager.'], 403);
        }

        $query = AuditLog::with('user');

        if ($request->has('action') && !empty($request->action)) {
            $query->where('action', $request->action);
        }

        if ($request->has('entity_type') && !empty($request->entity_type)) {
            $query->where('entity_type', $request->entity_type);
        }

        $logs = $query->latest()->limit(200)->get();

        return response()->json([
            'data' => $logs,
        ]);
    }
}
