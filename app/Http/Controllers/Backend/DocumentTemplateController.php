<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Mews\Purifier\Facades\Purifier;


class DocumentTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:document-generator-manage');
    }

    public function index(Request $request)
    {
        $categories = ['general', 'account_statement', 'loan_letter', 'welcome_letter', 'notification', 'compliance', 'marketing', 'external_bank_notification'];
        $query = DocumentTemplate::with('creator');

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $templates = $query->latest()->paginate(20);

        return view('backend.document_templates.index', compact('templates', 'categories'));
    }

    public function create()
    {
        $categories = ['general', 'account_statement', 'loan_letter', 'welcome_letter', 'notification', 'compliance', 'marketing', 'external_bank_notification'];
        return view('backend.document_templates.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email_from_name' => 'nullable|string|max:255',
            'category' => 'required|string|in:general,account_statement,loan_letter,welcome_letter,notification,compliance,marketing,external_bank_notification',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'email_subject' => 'nullable|string|max:255',
            'email_salutation' => 'nullable|string|max:255',
            'email_content' => 'nullable|string',
            'email_footer' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $cleanHtml = function($html) {
                if (empty($html)) return '';
                try {
                    return Purifier::clean($html);
                } catch (\Throwable $e) {
                    \Log::warning('Purifier warning: ' . $e->getMessage());
                    return $html;
                }
            };

            $content = $cleanHtml($request->input('content', ''));
            $emailContent = $cleanHtml($request->input('email_content', ''));
            $emailFooter = $cleanHtml($request->input('email_footer', ''));

            DocumentTemplate::create([
                'name' => $request->name,
                'email_from_name' => $request->email_from_name,
                'category' => $request->category,
                'description' => $request->description,
                'content' => $content,
                'email_subject' => $request->email_subject,
                'email_salutation' => $request->email_salutation,
                'email_content' => $emailContent,
                'email_footer' => $emailFooter,
                'is_active' => $request->has('is_active'),
                'created_by' => auth('admin')->id() ?? \App\Models\Admin::first()->id ?? null,
            ]);

            notify()->success('Document template created successfully.', 'Success');
            return redirect()->route('admin.document-template.index');
        } catch (\Throwable $e) {
            \Log::error('DocumentTemplate store error: ' . $e->getMessage());
            notify()->error('Error creating template: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    public function edit(DocumentTemplate $template)
    {
        $categories = ['general', 'account_statement', 'loan_letter', 'welcome_letter', 'notification', 'compliance', 'marketing', 'external_bank_notification'];
        return view('backend.document_templates.edit', compact('template', 'categories'));
    }

    public function update(Request $request, DocumentTemplate $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email_from_name' => 'nullable|string|max:255',
            'category' => 'required|string|in:general,account_statement,loan_letter,welcome_letter,notification,compliance,marketing,external_bank_notification',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'email_subject' => 'nullable|string|max:255',
            'email_salutation' => 'nullable|string|max:255',
            'email_content' => 'nullable|string',
            'email_footer' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $cleanHtml = function($html) {
                if (empty($html)) return '';
                try {
                    return Purifier::clean($html);
                } catch (\Throwable $e) {
                    \Log::warning('Purifier warning: ' . $e->getMessage());
                    return $html;
                }
            };

            $content = $cleanHtml($request->input('content', ''));
            $emailContent = $cleanHtml($request->input('email_content', ''));
            $emailFooter = $cleanHtml($request->input('email_footer', ''));

            $template->update([
                'name' => $request->name,
                'email_from_name' => $request->email_from_name,
                'category' => $request->category,
                'description' => $request->description,
                'content' => $content,
                'email_subject' => $request->email_subject,
                'email_salutation' => $request->email_salutation,
                'email_content' => $emailContent,
                'email_footer' => $emailFooter,
                'is_active' => $request->has('is_active'),
            ]);

            notify()->success('Document template updated successfully.', 'Success');
            return redirect()->route('admin.document-template.index');
        } catch (\Throwable $e) {
            \Log::error('DocumentTemplate update error: ' . $e->getMessage());
            notify()->error('Error updating template: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    public function destroy(DocumentTemplate $template)
    {
        $template->delete();
        notify()->success('Document template deleted successfully.', 'Success');
        return redirect()->route('admin.document-template.index');
    }

    public function loadTemplate(Request $request)
    {
        $template = DocumentTemplate::findOrFail($request->template_id);
        
        return response()->json([
            'title' => $template->name,
            'content' => $template->content,
            'email_subject' => $template->email_subject,
            'email_salutation' => $template->email_salutation,
            'email_content' => $template->email_content,
        ]);
    }
}
