<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganisationController extends Controller
{
    // Show form + table
    public function index()
    {
        $organisations = Organisation::orderBy('company_name')->get();

        return view('pages.master.organisation', compact('organisations'));
    }

    // Store new organisation
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'short_name'   => 'nullable|string|max:20',
            'cin'          => 'nullable|string|max:50',
            'address'      => 'nullable|string',
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('organisations', 'public');
        }

        Organisation::create($validated);

        return redirect()
            ->route('master.organisation')
            ->with('success', 'Organisation created successfully.');
    }

    // Get organisation for edit (AJAX)
    public function edit($id)
    {
        try {
            $organisation = Organisation::find($id);

            if (!$organisation) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Organisation not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'organisation' => $organisation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update organisation
    public function update(Request $request, $id)
    {
        $organisation = Organisation::findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'short_name'   => 'nullable|string|max:20',
            'cin'          => 'nullable|string|max:50',
            'address'      => 'nullable|string',
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($organisation->logo) {
                Storage::disk('public')->delete($organisation->logo);
            }
            $validated['logo'] = $request->file('logo')->store('organisations', 'public');
        }

        $organisation->update($validated);

        return redirect()
            ->route('master.organisation')
            ->with('success', 'Organisation updated successfully.');
    }

    // Delete organisation
    public function destroy($id)
    {
        $organisation = Organisation::findOrFail($id);

        // Delete logo file if exists
        if ($organisation->logo) {
            Storage::disk('public')->delete($organisation->logo);
        }

        $organisation->delete();

        return redirect()
            ->route('master.organisation')
            ->with('success', 'Organisation deleted successfully.');
    }
}