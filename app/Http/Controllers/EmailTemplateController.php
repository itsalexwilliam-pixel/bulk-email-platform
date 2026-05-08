<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $accountId = $this->getAccountId($request);

        $templates = EmailTemplate::forAccount($accountId)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $categories = $templates->pluck('category')->filter()->unique()->sort()->values();

        return view('templates.index', compact('templates', 'categories'));
    }

    public function create()
    {
        return view('templates.create');
    }

    public function store(Request $request)
    {
        $accountId = $this->getAccountId($request);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'subject'  => ['nullable', 'string', 'max:255'],
            'body'     => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        EmailTemplate::create([...$data, 'account_id' => $accountId]);

        return redirect()->route('templates.index')->with('success', 'Template saved successfully.');
    }

    public function edit(Request $request, EmailTemplate $template)
    {
        abort_if((int) $template->account_id !== $this->getAccountId($request), 403);

        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, EmailTemplate $template)
    {
        abort_if((int) $template->account_id !== $this->getAccountId($request), 403);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'subject'  => ['nullable', 'string', 'max:255'],
            'body'     => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $template->update($data);

        return redirect()->route('templates.index')->with('success', 'Template updated successfully.');
    }

    public function destroy(Request $request, EmailTemplate $template)
    {
        abort_if((int) $template->account_id !== $this->getAccountId($request), 403);

        $template->delete();

        return redirect()->route('templates.index')->with('success', 'Template deleted.');
    }

    /**
     * API endpoint: return template body+subject as JSON (for campaign editor Load modal).
     */
    public function show(Request $request, EmailTemplate $template)
    {
        abort_if((int) $template->account_id !== $this->getAccountId($request), 403);

        return response()->json([
            'id'      => $template->id,
            'name'    => $template->name,
            'subject' => $template->subject,
            'body'    => $template->body,
        ]);
    }
}
