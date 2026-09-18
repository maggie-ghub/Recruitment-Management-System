<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request)
    {
        $query = Department::with('subsidiary');

        // Subsidiary isolation or filter
        if ($request->has('subsidiary_id') && !empty($request->subsidiary_id)) {
            $query->where('subsidiary_id', $request->subsidiary_id);
        }

        // Subsidiary-scoped staff can only see their own department
        $user = $request->user();
        if ($user && $user->subsidiary_id) {
            $query->where('subsidiary_id', $user->subsidiary_id);
        }

        $departments = $query->latest()->get();

        return response()->json([
            'data' => $departments,
        ]);
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        $department = Department::create([
            'subsidiary_id' => $validated['subsidiary_id'],
            'name' => $validated['name'],
            'status' => $validated['status'] ?? 'active',
        ]);

        AuditLog::record('created', 'Department', $department->id, null, $department->toArray());

        return response()->json([
            'message' => 'Department created successfully.',
            'data' => $department->load('subsidiary'),
        ], 201);
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $before = $department->toArray();
        $department->update([
            'subsidiary_id' => $validated['subsidiary_id'],
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        AuditLog::record('updated', 'Department', $department->id, $before, $department->toArray());

        return response()->json([
            'message' => 'Department updated successfully.',
            'data' => $department->load('subsidiary'),
        ]);
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        $before = $department->toArray();
        $department->delete();

        AuditLog::record('deleted', 'Department', $department->id, $before, null);

        return response()->json([
            'message' => 'Department deleted successfully.',
        ]);
    }
}
