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

        // Document Generation Metrics
        $totalDocuments = DocumentHistory::whereBetween('created_at', [$startDate, $endDate])
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->count();
        
        $documentsByUser = DocumentHistory::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('user')
            ->get();

        // Email Metrics
        $totalEmailsSent = EmailTracking::whereBetween('created_at', [$startDate, $endDate])
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->count();
        
        $successfulEmails = EmailTracking::whereBetween('created_at', [$startDate, $endDate])
            ->successful()
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->count();
        
        $failedEmails = EmailTracking::whereBetween('created_at', [$startDate, $endDate])
            ->failed()
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->count();
        
        $openedEmails = EmailTracking::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('opened_at')
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->count();
        
        $clickedEmails = EmailTracking::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('clicked_at')
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->count();

        $emailDeliveryRate = $totalEmailsSent > 0 ? round(($successfulEmails / $totalEmailsSent) * 100, 2) : 0;
        $emailOpenRate = $successfulEmails > 0 ? round(($openedEmails / $successfulEmails) * 100, 2) : 0;
        $emailClickRate = $openedEmails > 0 ? round(($clickedEmails / $openedEmails) * 100, 2) : 0;

        // Template Usage
        $mostUsedTemplates = DocumentTemplate::withCount('documentHistories')
            ->orderByDesc('document_histories_count')
            ->limit(10)
            ->get();

        // Account Officer Performance
        $officerPerformance = DocumentHistory::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
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
        $emailStatusDistribution = EmailTracking::whereBetween('created_at', [$startDate, $endDate])
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Daily Trends
        $dailyDocumentTrend = DocumentHistory::whereBetween('created_at', [$startDate, $endDate])
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $dailyEmailTrend = EmailTracking::whereBetween('created_at', [$startDate, $endDate])
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
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
