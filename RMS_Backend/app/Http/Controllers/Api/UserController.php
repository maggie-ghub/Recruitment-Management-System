<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * FR-AUTH-004, FR-ADM-001: List internal staff and users with filters.
     */
    public function index(Request $request)
    {
        $query = User::with('subsidiary');

        // Filter by role
        if ($request->has('role') && !empty($request->role)) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by search query
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->get()->map(function ($u) {
            $u->is_locked = $u->isLocked();
            return $u;
        });

        return response()->json([
            'data' => $users,
        ]);
    }

    /**
     * FR-ADM-001: Create internal user (Recruiter, Hiring Manager, HR Manager, Admin).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:Admin,HR Manager,Recruiter,Hiring Manager,Applicant',
            'subsidiary_id' => 'nullable|exists:subsidiaries,id',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'subsidiary_id' => $validated['subsidiary_id'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        AuditLog::record('created', 'User', $user->id, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'subsidiary_id' => $user->subsidiary_id,
            'status' => $user->status,
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'data' => $user->load('subsidiary'),
        ], 201);
    }

    /**
     * FR-ADM-001: Update user details and role.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:Admin,HR Manager,Recruiter,Hiring Manager,Applicant',
            'subsidiary_id' => 'nullable|exists:subsidiaries,id',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $before = $user->only(['name', 'role', 'subsidiary_id', 'status']);
        
        $user->update([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'subsidiary_id' => $validated['subsidiary_id'] ?? null,
            'status' => $validated['status'],
        ]);

        AuditLog::record('updated', 'User', $user->id, $before, $user->only(['name', 'role', 'subsidiary_id', 'status']));

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => $user->load('subsidiary'),
        ]);
    }

    /**
     * FR-AUTH-005, NFR-SEC-006: Activate, Deactivate, or Suspend internal account.
     */
    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $before = ['status' => $user->status];
        $user->status = $validated['status'];
        if ($user->status === 'active') {
            $user->locked_until = null;
            $user->failed_login_attempts = 0;
        }
        $user->save();

        AuditLog::record('status_change', 'User', $user->id, $before, ['status' => $user->status]);

        return response()->json([
            'message' => "User status updated to {$user->status}.",
            'data' => $user,
        ]);
    }
}
