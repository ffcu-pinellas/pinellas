<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentGeneratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:document-generator-manage');
    }

    public function index()
    {
        $users = User::where('status', 1)->get();
        return view('backend.document_generator.index', compact('users'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $user = null;
        if ($request->filled('user_id')) {
            $user = User::find($request->user_id);
        }

        $content = $request->input('content');
        $title = $request->input('title');
        
        // Replace dynamic variables if user is selected
        if ($user) {
            $content = str_replace('[USER_NAME]', $user->full_name, $content);
            $content = str_replace('[USER_ADDRESS]', $user->address ?? 'NO ADDRESS ON FILE', $content);
            $content = str_replace('[USER_ACCOUNT_NUMBER]', $user->account_number ?? '', $content);
            $content = str_replace('[USER_BALANCE]', setting('currency_symbol', 'global') . number_format($user->balance, 2), $content);
        }

        // Base64 Logo for PDF rendering
        $logoBase64 = null;
        try {
            $logoUrl = 'https://www.pinellasfcu.org/templates/pinellas/images/logo.png';
            $logoData = curl_get_file_contents($logoUrl);
            if ($logoData) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }
        } catch (\Exception $e) {
            \Log::error("Document PDF Logo Fetch Error: " . $e->getMessage());
        }

        $pdf = Pdf::loadView('backend.document_generator.pdf', compact('title', 'content', 'user', 'logoBase64'));
        
        $filename = 'Document_' . \Str::slug($title) . '_' . now()->format('YmdHis') . '.pdf';

        if ($request->input('action') === 'preview') {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}
