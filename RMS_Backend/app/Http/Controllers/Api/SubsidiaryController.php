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
            'logo_url' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        $logoUrl = $validated['logo_url'] ?? null;
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('company_logos', 'public');
            $logoUrl = '/storage/' . $path;
        }

        $subsidiary = Subsidiary::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'status' => $validated['status'] ?? 'active',
            'logo_url' => $logoUrl,
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
            'logo_url' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        $logoUrl = $validated['logo_url'] ?? $subsidiary->logo_url;
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('company_logos', 'public');
            $logoUrl = '/storage/' . $path;
        }

        $before = $subsidiary->toArray();
        $subsidiary->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'status' => $validated['status'],
            'logo_url' => $logoUrl,
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

    /**
     * Stream subsidiary logo securely or fallback.
     */
    public function logo(Subsidiary $subsidiary)
    {
        if (!$subsidiary->logo_url) {
            return response()->json(['message' => 'No logo available.'], 404);
        }

        $relativePath = str_replace('/storage/', '', $subsidiary->logo_url);
        $fullPath = storage_path('app/public/' . $relativePath);
        if (!file_exists($fullPath)) {
            return response()->json(['message' => 'Logo file not found on disk.'], 404);
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];
        $contentType = $mimes[$ext] ?? 'image/png';
        $fileSize = filesize($fullPath);

        return response()->stream(function () use ($fullPath) {
            $stream = fopen($fullPath, 'rb');
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $contentType,
            'Content-Length' => $fileSize,
            'Cache-Control' => 'public, max-age=86400',
            'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
        ]);
    }
}
