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

        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(5, min(100, $perPage)); // clamp between 5 and 100

        $query = AuditLog::with('user:id,name,email');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%'))
                ->orWhere('action', 'like', '%' . $request->search . '%')
                ->orWhere('entity_type', 'like', '%' . $request->search . '%');
        }

        $paginated = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
        ]);
    }
}

