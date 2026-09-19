<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Subsidiary;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'logo' => 'nullable',
        ]);

        $logoUrl = $validated['logo_url'] ?? null;
        if ($request->hasFile('logo')) {
            $logoUrl = $this->storeLogo($request->file('logo'));
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
            'logo' => 'nullable',
        ]);

        $logoUrl = $validated['logo_url'] ?? $subsidiary->logo_url;
        if ($request->hasFile('logo')) {
            $logoUrl = $this->storeLogo($request->file('logo'));
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

    private function validateLogo($file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];

        if (!in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages(['logo' => 'The logo must be a PNG, JPG, GIF, SVG, or WEBP image.']);
        }

        if ($extension === 'svg') {
            $contents = file_get_contents($file->getRealPath());
            if ($contents === false || !preg_match('/<svg\b/i', $contents) || preg_match('/<script\b|\bon[a-z]+\s*=/i', $contents)) {
                throw ValidationException::withMessages(['logo' => 'The uploaded SVG logo is invalid.']);
            }
        } elseif (@getimagesize($file->getRealPath()) === false) {
            throw ValidationException::withMessages(['logo' => 'The uploaded file is not a valid image.']);
        }
    }

    private function validateLogoUpload($file): void
    {
        if (!$file->isValid() || $file->getSize() > 5 * 1024 * 1024) {
            throw ValidationException::withMessages(['logo' => 'The logo upload failed or exceeds the 5MB limit.']);
        }
    }

    private function storeLogo($file): string
    {
        $this->validateLogoUpload($file);
        $this->validateLogo($file);

        $directory = storage_path('app/public/company_logos');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create the company logo storage directory.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid() . '.' . $extension;
        $file->move($directory, $filename);

        return '/storage/company_logos/' . $filename;
    }
}
