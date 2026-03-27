<!-- Premium Account Details Modal -->
<div class="modal fade" id="accountDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 30px; background: #fff; overflow: hidden;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center">
                    <div class="account-icon-wrap me-3" style="width: 50px; height: 50px; background: #f0f7ff; border-radius: 15px; display: flex; align-items: center; justify-content: center; color: var(--account-card-primary-background-color);">
                        <i class="fas fa-university fa-lg"></i>
                    </div>
                    <div>
                        <h4 class="modal-title fw-bold m-0" id="detailModalTitle" style="color: #1a1a1a;">Account Name</h4>
                        <div class="text-muted small" id="detailModalAccNum">Account Number</div>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row align-items-center">
                    <!-- Chart Column -->
                    <div class="col-lg-6 col-12 text-center mb-4 mb-lg-0">
                        <div id="accountDonutChart" style="min-height: 250px;"></div>
                        <div class="chart-center-label" style="margin-top: -150px; padding-bottom: 120px;">
                            <div class="text-muted small fw-bold text-uppercase" id="chartLabelText">Total Limit</div>
                            <div class="h4 fw-bold m-0" id="chartLabelValue">$0.00</div>
                        </div>
                    </div>
                    <!-- Data Column -->
                    <div class="col-lg-6 col-12">
                        <div class="detail-metrics-grid d-flex flex-column gap-3">
                            <!-- Balance Card -->
                            <div class="metric-card p-3" style="background: #f8fbfe; border-radius: 20px; border: 1px solid #eef4f9;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="m-icon" style="color: #00ce8d;"><i class="fas fa-wallet"></i></div>
                                    <div>
                                        <div class="text-muted small fw-600" id="metric1Label">Current Balance</div>
                                        <div class="h5 fw-bold m-0" id="metric1Value">$0.00</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Available Card -->
                            <div class="metric-card p-3" style="background: #f8fbfe; border-radius: 20px; border: 1px solid #eef4f9;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="m-icon" style="color: var(--account-card-primary-background-color);"><i class="fas fa-check-circle"></i></div>
                                    <div>
                                        <div class="text-muted small fw-600" id="metric2Label">Available Funds</div>
                                        <div class="h5 fw-bold m-0" id="metric2Value">$0.00</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Helper Text -->
                            <div class="p-2 text-center mt-2">
                                <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1 opacity-75"></i> As of {{ date('M j, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 bg-light bg-opacity-50">
                <div class="d-flex gap-2 w-100">
                    <a href="{{ route('user.transactions') }}" class="btn btn-outline-primary flex-grow-1 rounded-pill py-2 fw-bold" style="border-width: 2px;">View Activity</a>
                    <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-primary flex-grow-1 rounded-pill py-2 fw-bold shadow-sm">Transfer Funds</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('style')
<style>
    .m-icon {
        width: 40px;
        height: 40px;
        background: #fff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
    }
    .metric-card {
        transition: transform 0.2s ease;
    }
    .metric-card:hover {
        transform: scale(1.02);
    }
</style>
@endpush

@push('js')
<script src="{{ asset('assets/front/js/apexcharts.js') }}"></script>
<script>
    if (typeof accountChart === 'undefined') {
        var accountChart = null;
    }

    function showAccountDetails(title, accNum, balance, limit, type) {
        const modalEl = document.getElementById('accountDetailModal');
        const modal = new bootstrap.Modal(modalEl);
        
        document.getElementById('detailModalTitle').innerText = title;
        document.getElementById('detailModalAccNum').innerText = accNum;
        
        const currency = "{{ setting('currency_symbol','$') }}";
        
        // Populate Metrics
        if (type === 'loan') {
            document.getElementById('metric1Label').innerText = "Remaining Balance";
            document.getElementById('metric2Label').innerText = "Paid Principal";
            document.getElementById('metric1Value').innerText = currency + balance.toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('metric2Value').innerText = currency + (limit - balance).toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('chartLabelText').innerText = "Original Loan";
            document.getElementById('chartLabelValue').innerText = currency + limit.toLocaleString('en-US', {minimumFractionDigits: 2});
        } else if (type === 'ira') {
            document.getElementById('metric1Label').innerText = "Current Balance";
            document.getElementById('metric2Label').innerText = "Available Cash";
            document.getElementById('metric1Value').innerText = currency + balance.toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('metric2Value').innerText = currency + balance.toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('chartLabelText').innerText = "Total Value";
            document.getElementById('chartLabelValue').innerText = currency + balance.toLocaleString('en-US', {minimumFractionDigits: 2});
        } else {
            // HELOC or CC
            document.getElementById('metric1Label').innerText = "Current Balance";
            document.getElementById('metric2Label').innerText = "Available Credit";
            document.getElementById('metric1Value').innerText = currency + balance.toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('metric2Value').innerText = currency + (limit - balance).toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('chartLabelText').innerText = "Total Line";
            document.getElementById('chartLabelValue').innerText = currency + limit.toLocaleString('en-US', {minimumFractionDigits: 2});
        }

        modal.show();

        // Render Chart
        setTimeout(() => {
            renderDonutChart(balance, limit, type);
        }, 350);
    }

    function renderDonutChart(balance, limit, type) {
        if (accountChart) {
            accountChart.destroy();
        }

        let series = [];
        let labels = [];
        let colors = [];

        if (type === 'loan') {
            series = [limit - balance, balance];
            labels = ["Paid", "Remaining"];
            colors = ['#00ce8d', '#00549b']; // Green/Blue
        } else if (type === 'ira') {
            series = [balance];
            labels = ["Total Value"];
            colors = ['#00549b'];
        } else {
            series = [balance, limit - balance];
            labels = ["Spent", "Available"];
            colors = ['#ff3b30', '#00ce8d']; // Red/Green
        }

        const options = {
            series: series,
            chart: {
                type: 'donut',
                height: 250,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                }
            },
            labels: labels,
            colors: colors,
            legend: {
                show: true,
                position: 'bottom'
            },
            dataLabels: {
                enabled: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        background: 'transparent',
                    }
                }
            },
            stroke: {
                width: 0
            }
        };

        accountChart = new ApexCharts(document.querySelector("#accountDonutChart"), options);
        accountChart.render();
    }
</script>
@endpush
