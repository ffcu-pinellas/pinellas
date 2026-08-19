@extends('backend.layouts.app')
@section('title')
    {{ __('Document Generator Analytics') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('Document & Email Analytics') }}</h2>
                        </div>
                    </div>
                    <div class="col-auto">
                        <form action="{{ route('admin.document-analytics.index') }}" method="get" id="dateFilterForm">
                            <select class="form-select" name="date_range" onchange="this.form.submit()">
                                <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>{{ __('Last 7 Days') }}</option>
                                <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>{{ __('Last 30 Days') }}</option>
                                <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>{{ __('Last 90 Days') }}</option>
                                <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>{{ __('Last Year') }}</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <!-- Stats Row -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="site-card">
                        <div class="site-card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">{{ $totalDocuments }}</h3>
                                <p class="mb-0 text-muted">{{ __('Documents Generated') }}</p>
                            </div>
                            <div class="icon-box bg-primary text-white p-3 rounded">
                                <i class="ant-file-text fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="site-card">
                        <div class="site-card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">{{ $totalEmailsSent }}</h3>
                                <p class="mb-0 text-muted">{{ __('Emails Dispatched') }}</p>
                            </div>
                            <div class="icon-box bg-info text-white p-3 rounded">
                                <i class="ant-mail fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="site-card">
                        <div class="site-card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1 text-success">{{ $emailOpenRate }}%</h3>
                                <p class="mb-0 text-muted">{{ __('Email Open Rate') }}</p>
                            </div>
                            <div class="icon-box bg-success text-white p-3 rounded">
                                <i class="ant-eye fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="site-card">
                        <div class="site-card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1 text-warning">{{ $emailDeliveryRate }}%</h3>
                                <p class="mb-0 text-muted">{{ __('Delivery Success') }}</p>
                            </div>
                            <div class="icon-box bg-warning text-white p-3 rounded">
                                <i class="ant-check-circle fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Trends Chart -->
                <div class="col-xl-8">
                    <div class="site-card h-100">
                        <div class="site-card-header">
                            <h3 class="title">{{ __('Activity Trends') }}</h3>
                        </div>
                        <div class="site-card-body">
                            <canvas id="activityTrendChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Status Distribution -->
                <div class="col-xl-4">
                    <div class="site-card h-100">
                        <div class="site-card-header">
                            <h3 class="title">{{ __('Email Status Distribution') }}</h3>
                        </div>
                        <div class="site-card-body">
                            <canvas id="emailStatusChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Top Templates -->
                <div class="col-xl-6">
                    <div class="site-card h-100">
                        <div class="site-card-header">
                            <h3 class="title">{{ __('Most Used Templates') }}</h3>
                        </div>
                        <div class="site-card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Template Name') }}</th>
                                        <th>{{ __('Usage Count') }}</th>
                                        <th>{{ __('Category') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mostUsedTemplates as $template)
                                        <tr>
                                            <td>{{ $template->name }}</td>
                                            <td>{{ $template->document_histories_count }}</td>
                                            <td><span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $template->category)) }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top Recipients -->
                <div class="col-xl-6">
                    <div class="site-card h-100">
                        <div class="site-card-header">
                            <h3 class="title">{{ __('Top Recipients (By Volume)') }}</h3>
                        </div>
                        <div class="site-card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Documents Received') }}</th>
                                        <th>{{ __('Account Number') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documentsByUser as $stat)
                                        <tr>
                                            <td>{{ $stat->user ? $stat->user->full_name : __('Deleted User') }}</td>
                                            <td>{{ $stat->count }}</td>
                                            <td>{{ $stat->user ? $stat->user->account_number : 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if(auth('admin')->check() && auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'))
            <div class="row mt-4">
                <!-- Officer Performance -->
                <div class="col-xl-12">
                    <div class="site-card">
                        <div class="site-card-header">
                            <h3 class="title">{{ __('Account Officer Performance') }}</h3>
                        </div>
                        <div class="site-card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Officer Name') }}</th>
                                        <th>{{ __('Documents Generated') }}</th>
                                        <th>{{ __('Unique Customers Served') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($officerPerformance as $perf)
                                        <tr>
                                            <td><strong>{{ $perf['officer_name'] }}</strong></td>
                                            <td>{{ $perf['documents_generated'] }}</td>
                                            <td>{{ $perf['unique_users'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Activity Trend Chart
            var trendCtx = document.getElementById('activityTrendChart').getContext('2d');
            var trendLabels = {!! json_encode($dailyDocumentTrend->keys()) !!};
            var documentData = {!! json_encode($dailyDocumentTrend->values()) !!};
            var emailData = {!! json_encode($dailyEmailTrend->values()) !!};

            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [
                        {
                            label: 'Documents',
                            data: documentData,
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Emails',
                            data: emailData,
                            borderColor: '#1cc88a',
                            backgroundColor: 'rgba(28, 200, 138, 0.05)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });

            // Email Status Chart
            var statusCtx = document.getElementById('emailStatusChart').getContext('2d');
            var statusLabels = {!! json_encode($emailStatusDistribution->keys()) !!};
            var statusData = {!! json_encode($emailStatusDistribution->values()) !!};
            
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                    datasets: [{
                        data: statusData,
                        backgroundColor: ['#1cc88a', '#e74a3b', '#f6c23e', '#4e73df', '#858796'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
@endsection
