<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\DocumentHistory;
use App\Models\EmailTracking;
use App\Models\DocumentTemplate;
use App\Models\Admin;
use Illuminate\Http\Request;

class DocumentAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:document-generator-manage');
    }

    public function index(Request $request)
    {
        $dateRange = $request->get('date_range', '30'); // Default to last 30 days
        $startDate = now()->subDays($dateRange)->startOfDay();
        $endDate = now()->endOfDay();

        // Document Generation Base Query
        $docBase = DocumentHistory::whereBetween('created_at', [$startDate, $endDate])
            ->when(auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth('admin')->id());
                });
            });
        if (function_exists('admin_institution_scope')) {
            admin_institution_scope($docBase, 'user');
        }

        $totalDocuments = (clone $docBase)->count();
        
        $documentsByUser = (clone $docBase)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('user')
            ->get();

        // Email Base Query
        $emailBase = EmailTracking::whereBetween('created_at', [$startDate, $endDate])
            ->when(auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth('admin')->id());
                });
            });
        if (function_exists('admin_institution_scope')) {
            admin_institution_scope($emailBase, 'user');
        }

        $totalEmailsSent = (clone $emailBase)->count();
        $successfulEmails = (clone $emailBase)->successful()->count();
        $failedEmails = (clone $emailBase)->failed()->count();
        $openedEmails = (clone $emailBase)->whereNotNull('opened_at')->count();
        $clickedEmails = (clone $emailBase)->whereNotNull('clicked_at')->count();

        $emailDeliveryRate = $totalEmailsSent > 0 ? round(($successfulEmails / $totalEmailsSent) * 100, 2) : 0;
        $emailOpenRate = $successfulEmails > 0 ? round(($openedEmails / $successfulEmails) * 100, 2) : 0;
        $emailClickRate = $openedEmails > 0 ? round(($clickedEmails / $openedEmails) * 100, 2) : 0;

        // Template Usage
        $mostUsedTemplates = DocumentTemplate::withCount('documentHistories')
            ->orderByDesc('document_histories_count')
            ->limit(10)
            ->get();

        // Account Officer Performance
        $officerPerformance = (clone $docBase)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(*) as documents_generated')
            ->groupBy('user_id')
            ->with('user')
            ->get()
            ->map(function ($item) {
                $user = $item->user;
                if ($user && $user->staff_id) {
                    $officer = Admin::find($user->staff_id);
                    $item->officer_name = $officer ? $officer->name : 'Unassigned';
                    $item->officer_id = $officer ? $officer->id : null;
                } else {
                    $item->officer_name = 'Unassigned';
                    $item->officer_id = null;
                }
                return $item;
            })
            ->groupBy('officer_id')
            ->map(function ($group) {
                return [
                    'officer_name' => $group->first()->officer_name,
                    'officer_id' => $group->first()->officer_id,
                    'documents_generated' => $group->sum('documents_generated'),
                    'unique_users' => $group->count(),
                ];
            })
            ->sortByDesc('documents_generated')
            ->take(10);

        // Email Status Distribution
        $emailStatusDistribution = (clone $emailBase)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Daily Trends
        $dailyDocumentTrend = (clone $docBase)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $dailyEmailTrend = (clone $emailBase)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        return view('backend.document_analytics.index', compact(
            'totalDocuments',
            'documentsByUser',
            'totalEmailsSent',
            'successfulEmails',
            'failedEmails',
            'openedEmails',
            'clickedEmails',
            'emailDeliveryRate',
            'emailOpenRate',
            'emailClickRate',
            'mostUsedTemplates',
            'officerPerformance',
            'emailStatusDistribution',
            'dailyDocumentTrend',
            'dailyEmailTrend',
            'dateRange'
        ));
    }
}
