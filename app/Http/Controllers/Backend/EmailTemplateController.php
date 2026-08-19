<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Traits\ImageUpload;
use Illuminate\Http\Request;
use Validator;

class EmailTemplateController extends Controller
{
    use ImageUpload;

    public function __construct()
    {
        $this->middleware('permission:email-template');
    }

    public function index(Request $request)
    {
        \App\Services\EmailTemplateAutoSync::sync();
        $perPage = $request->perPage ?? 15;
        $order = $request->order ?? 'asc';
        $search = $request->search ?? null;
        $status = $request->status ?? 'all';
        $emails = EmailTemplate::order($order)
            ->search($search)
            ->status($status)
            ->paginate($perPage);

        return view('backend.email.template', compact('emails'));
    }

    public function edit($id)
    {
        $template = EmailTemplate::find($id);

        return view('backend.email.edit', compact('template'));
    }

    public function create()
    {
        return view('backend.email.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required|unique:email_templates,code',
            'subject' => 'required',
            'message_body' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back()->withInput();
        }

        $input = $request->all();
        $isHtml = str_contains($input['message_body'], '<') && str_contains($input['message_body'], '>');

        $data = [
            'name' => $input['name'],
            'code' => \Illuminate\Support\Str::slug($input['code'], '_'),
            'for' => $input['for'] ?? 'User',
            'subject' => $input['subject'],
            'message_body' => $isHtml ? $input['message_body'] : nl2br($input['message_body']),
            'title' => $input['title'] ?? null,
            'salutation' => $input['salutation'] ?? null,
            'button_level' => $input['button_level'] ?? null,
            'button_link' => $input['button_link'] ?? null,
            'footer_status' => $input['footer_status'] ?? 0,
            'footer_body' => $input['footer_body'] ?? null,
            'bottom_status' => $input['bottom_status'] ?? 0,
            'bottom_title' => $input['bottom_title'] ?? null,
            'bottom_body' => $input['bottom_body'] ?? null,
            'short_codes' => $input['short_codes'] ?? null,
            'status' => $input['status'] ?? 1,
        ];

        if (isset($input['banner']) && is_file($input['banner'])) {
            $data['banner'] = self::imageUploadTrait($input['banner']);
        }

        EmailTemplate::create($data);

        notify()->success(__('Email Template Created Successfully'));

        return redirect()->route('admin.email-template');
    }

    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'message_body' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back();
        }

        $input = $request->all();
        $isHtml = str_contains($input['message_body'], '<') && str_contains($input['message_body'], '>');

        $data = [
            'subject' => $input['subject'],
            'message_body' => $isHtml ? $input['message_body'] : nl2br($input['message_body']),
            'title' => $input['title'] ?? null,
            'salutation' => $input['salutation'] ?? null,
            'button_level' => $input['button_level'] ?? null,
            'button_link' => $input['button_link'] ?? null,
            'footer_status' => $input['footer_status'] ?? 0,
            'footer_body' => !empty($input['footer_body']) ? (str_contains($input['footer_body'], '<') ? $input['footer_body'] : nl2br($input['footer_body'])) : null,
            'bottom_status' => $input['bottom_status'] ?? 0,
            'bottom_title' => $input['bottom_title'] ?? null,
            'bottom_body' => !empty($input['bottom_body']) ? (str_contains($input['bottom_body'], '<') ? $input['bottom_body'] : nl2br($input['bottom_body'])) : null,
            'status' => $input['status'] ?? 1,
        ];

        $template = EmailTemplate::find($input['id']);
        if (isset($input['banner']) && is_file($input['banner'])) {
            $data['banner'] = self::imageUploadTrait($input['banner'], $template->banner);
        }

        $template->update($data);

        notify()->success(__('Email Template Updated Successfully'));

        return redirect()->route('admin.email-template');
    }
}
