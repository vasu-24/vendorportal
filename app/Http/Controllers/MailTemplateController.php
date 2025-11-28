<?php

namespace App\Http\Controllers;

use App\Models\MailTemplate;
use Illuminate\Http\Request;

class MailTemplateController extends Controller
{
    // Display all templates
    public function index()
    {
        $templates = MailTemplate::orderBy('created_at', 'desc')->get();
        return view('pages.master.template', compact('templates'));
    }

    // Store new template
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        MailTemplate::create($request->all());

        return redirect()->route('master.template')->with('success', 'Template created successfully!');
    }

    // Get single template (for editing)
    public function show($id)
    {
        $template = MailTemplate::findOrFail($id);
        return response()->json($template);
    }

    // Update template
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        $template = MailTemplate::findOrFail($id);
        $template->update($request->all());

        return redirect()->route('master.template')->with('success', 'Template updated successfully!');
    }

    // Delete template
    public function destroy($id)
    {
        $template = MailTemplate::findOrFail($id);
        $template->delete();

        return response()->json(['success' => true, 'message' => 'Template deleted successfully!']);
    }
}