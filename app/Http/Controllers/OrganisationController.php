<?php

namespace App\Http\Controllers;
use App\Http\Controllers\OrganisationController;

use App\Models\Organisation;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    // Show form + table
    public function index()
    {
        $organisations = Organisation::orderBy('company_name')->get();

        return view('pages.master.organisation', compact('organisations'));
    }

    // Store new organisation row
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'cin'          => 'nullable|string|max:50',
            'address'      => 'nullable|string',
        ]);

        Organisation::create($validated);

        return redirect()
           ->route('master.organisation')
            ->with('success', 'Organisation saved successfully.');
    }
}
