<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Subsidiary;
use Illuminate\Http\Request;

class SubsidiaryController extends Controller
{
    /**
     * Display a listing of subsidiaries / companies.
     */
    public function index(Request $request)
    {
        $query = Subsidiary::withCount('departments');

        if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        $subsidiaries = $query->latest()->get();

        return response()->json([
            'data' => $subsidiaries,
        ]);
    }

    /**
     * Store a newly created subsidiary / company.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:subsidiaries,code',
            'status' => 'nullable|in:active,inactive',
        ]);

        $subsidiary = Subsidiary::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'status' => $validated['status'] ?? 'active',
        ]);

        AuditLog::record('created', 'Subsidiary', $subsidiary->id, null, $subsidiary->toArray());

        return response()->json([
            'message' => 'Company created successfully.',
            'data' => $subsidiary,
        ], 201);
    }

    /**
     * Display the specified subsidiary / company.
     */
    public function show(Subsidiary $subsidiary)
    {
        return response()->json([
            'data' => $subsidiary->load('departments'),
        ]);
    }

    /**
     * Update the specified subsidiary / company.
     */
    public function update(Request $request, Subsidiary $subsidiary)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:subsidiaries,code,' . $subsidiary->id,
            'status' => 'required|in:active,inactive',
        ]);

        $before = $subsidiary->toArray();
        $subsidiary->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'status' => $validated['status'],
        ]);

        AuditLog::record('updated', 'Subsidiary', $subsidiary->id, $before, $subsidiary->toArray());

        return response()->json([
            'message' => 'Company updated successfully.',
            'data' => $subsidiary,
        ]);
    }

    /**
     * Remove the specified subsidiary / company.
     */
    public function destroy(Subsidiary $subsidiary)
    {
        $before = $subsidiary->toArray();
        $subsidiary->delete();

        AuditLog::record('deleted', 'Subsidiary', $subsidiary->id, $before, null);

        return response()->json([
            'message' => 'Company deleted successfully.',
        ]);
    }
}
