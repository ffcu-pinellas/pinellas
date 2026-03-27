<div class="modal fade" id="accountDetailModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl overflow-hidden" style="border-radius: 28px; background: linear-gradient(180deg, #f8fbfe 0%, #ffffff 100%);">
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div id="modal-type-icon" class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: rgba(0, 84, 155, 0.1); color: #00549b;">
                        <i class="fas fa-university"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalAccountTitle" style="color: #0d1e3a; font-size: 1.25rem;">Account Details</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small" id="modalAccountNumber" data-full-number="" style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">x0000</span>
                            <button type="button" class="btn btn-link p-0 text-primary text-decoration-none small fw-bold" onclick="window.copyAccountNumber()" style="font-size: 0.75rem;">
                                <i class="far fa-copy me-1"></i>Copy
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" style="background-color: rgba(0,0,0,0.05); border-radius: 50%; padding: 0.75rem;"></button>
            </div>

            <div class="modal-body p-4 pt-2">
                <div class="row align-items-center">
                    <!-- Donut Chart Column -->
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div id="accountChart" style="min-height: 250px;"></div>
                    </div>
                    
                    <!-- Metrics Column -->
                    <div class="col-md-6">
                        <div class="metrics-container d-flex flex-column gap-3">
                            <!-- Card 1 -->
                            <div class="metric-card p-3 rounded-4 shadow-sm border border-white" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="m-icon rounded-circle d-flex align-items-center justify-content-center" id="card1-icon" style="width: 40px; height: 40px; background: rgba(0, 206, 141, 0.1); color: #00ce8d;">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-semibold" id="card1-label">Current Balance</div>
                                        <div class="h5 fw-bold mb-0 text-dark" id="card1-value">$0.00</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Card 2 -->
                            <div class="metric-card p-3 rounded-4 shadow-sm border border-white" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="m-icon rounded-circle d-flex align-items-center justify-content-center" id="card2-icon" style="width: 40px; height: 40px; background: rgba(0, 84, 155, 0.1); color: #00549b;">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-semibold" id="card2-label">Available Credit</div>
                                        <div class="h5 fw-bold mb-0 text-dark" id="card2-value">$0.00</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Expandable Account Info -->
                            <div class="mt-2">
                                <button class="btn btn-light w-100 rounded-pill py-2 text-start px-3 shadow-none border-0 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#accountInfoCollapse">
                                    <span class="small fw-bold text-secondary"><i class="fas fa-info-circle me-2 opacity-75"></i>Account Information</span>
                                    <i class="fas fa-chevron-down small text-muted"></i>
                                </button>
                                <div class="collapse mt-2" id="accountInfoCollapse">
                                    <div class="bg-white rounded-4 p-3 border shadow-sm">
                                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-light">
                                            <span class="small text-muted">Interest Rate (APY)</span>
                                            <span class="small fw-bold text-dark">0.25%</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-light">
                                            <span class="small text-muted">Routing Number</span>
                                            <span class="small fw-bold text-dark">063192257</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="small text-muted">Next Statement</span>
                                            <span class="small fw-bold text-dark">{{ \Carbon\Carbon::now()->endOfMonth()->format('M j, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-2 text-center mt-3">
                    <p class="text-muted" style="font-size: 0.75rem;"><i class="fas fa-clock me-1 opacity-50"></i> Last updated: {{ date('M j, Y g:i A') }}</p>
                </div>
            </div>

            <!-- Footer-like Actions -->
            <div class="px-4 pb-4 pt-2">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('user.transactions') }}" id="btnViewActivity" class="btn btn-outline-primary w-100 rounded-pill py-2 fw-bold" style="border-width: 2px;">View Activity</a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('user.fund_transfer.index') }}" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-lg" style="background: #00549b; border: none;">Transfer Funds</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Premium Toast Notification -->
<div id="copyToast" class="premium-toast">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-check-circle text-success"></i>
        <span>Copied to clipboard</span>
    </div>
</div>

<style>
    #accountDetailModal .metric-card { transition: all 0.2s ease; }
    #accountDetailModal .metric-card:hover { transform: scale(1.02); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; }
    .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }

    .premium-toast {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: rgba(13, 30, 58, 0.95);
        color: white;
        padding: 12px 24px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        z-index: 9999;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        backdrop-filter: blur(8px);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        opacity: 0;
        pointer-events: none;
    }
    .premium-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
</style>

@push('js')
<script>
    // Critical: Define showAccountDetails ASAP
    window.accountChartInstance = null;

    window.copyAccountNumber = function() {
        const el = document.getElementById('modalAccountNumber');
        if (!el) return;
        const accNum = el.getAttribute('data-full-number') || el.innerText.replace('x', '');
        
        const showToast = () => {
            const toast = document.getElementById('copyToast');
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(accNum).then(() => {
                showToast();
            }).catch(err => {
                console.error('Clipboard error:', err);
                // Fallback inside catch
                copyFallback(accNum);
            });
        } else {
            copyFallback(accNum);
        }
        
        function copyFallback(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast();
        }
    };

    window.showAccountDetails = function(title, number, balance, limit, type) {
        console.log("Triggering Account Details:", {title, number, balance, limit, type});
        
        const modalElem = document.getElementById('accountDetailModal');
        if (!modalElem) {
            console.error("Account detail modal element not found!");
            return;
        }

        // Set Header & Data
        document.getElementById('modalAccountTitle').innerText = title;
        const numEl = document.getElementById('modalAccountNumber');
        numEl.innerText = 'x' + number.toString().slice(-4);
        numEl.setAttribute('data-full-number', number);
        
        // Update View Activity Link
        const baseActivityUrl = "{{ route('user.transactions') }}";
        const viewActivityBtn = document.getElementById('btnViewActivity');
        if (viewActivityBtn) {
            viewActivityBtn.href = baseActivityUrl + "?wallet=" + type;
        }

        const currency = "{{ setting('currency_symbol','$') }}";
        const fmt = (val) => currency + new Intl.NumberFormat().format(parseFloat(val || 0).toFixed(2));

        // Display Logic
        let series = [];
        let labels = [];
        let colors = [];
        let totalValue = parseFloat(balance || 0);
        let totalLabel = "Total Value";

        if (type === 'cc' || type === 'heloc') {
            const spent = parseFloat(balance || 0);
            const available = parseFloat(limit || 0) - spent;
            series = [spent, available];
            labels = ['Used', 'Available'];
            colors = ['#e31837', '#00ce8d'];
            totalValue = parseFloat(limit || 0);
            totalLabel = "Credit Line";
            document.getElementById('card1-label').innerText = "Current Balance";
            document.getElementById('card1-value').innerText = fmt(spent);
            document.getElementById('card1-icon').style.color = "#e31837";
            document.getElementById('card1-icon').style.background = "rgba(227, 24, 55, 0.1)";
            document.getElementById('card2-label').innerText = "Available Credit";
            document.getElementById('card2-value').innerText = fmt(available);
            document.getElementById('card2-icon').style.color = "#00ce8d";
            document.getElementById('card2-icon').style.background = "rgba(0, 206, 141, 0.1)";
        } else if (type === 'loan') {
            const paid = parseFloat(limit || 0) - parseFloat(balance || 0);
            const remaining = parseFloat(balance || 0);
            series = [paid, remaining];
            labels = ['Paid Off', 'Remaining'];
            colors = ['#00ce8d', '#00549b'];
            totalValue = parseFloat(limit || 0);
            totalLabel = "Original Loan";
            document.getElementById('card1-label').innerText = "Remaining Principal";
            document.getElementById('card1-value').innerText = fmt(remaining);
            document.getElementById('card1-icon').style.color = "#00549b";
            document.getElementById('card1-icon').style.background = "rgba(0, 84, 155, 0.1)";
            document.getElementById('card2-label').innerText = "Amount Paid";
            document.getElementById('card2-value').innerText = fmt(paid);
            document.getElementById('card2-icon').style.color = "#00ce8d";
            document.getElementById('card2-icon').style.background = "rgba(0, 206, 141, 0.1)";
        } else {
            series = [parseFloat(balance || 0)];
            labels = ['Total Balance'];
            colors = ['#00549b'];
            totalValue = parseFloat(balance || 0);
            totalLabel = "Total Value";
            document.getElementById('card1-label').innerText = "Current Balance";
            document.getElementById('card1-value').innerText = fmt(balance);
            document.getElementById('card1-icon').style.color = "#00ce8d";
            document.getElementById('card1-icon').style.background = "rgba(0, 206, 141, 0.1)";
            document.getElementById('card2-label').innerText = "Available Cash";
            document.getElementById('card2-value').innerText = fmt(balance);
            document.getElementById('card2-icon').style.color = "#00549b";
            document.getElementById('card2-icon').style.background = "rgba(0, 84, 155, 0.1)";
        }

        // Show Modal ASAP
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalElem).show();
            } else {
                $(modalElem).modal('show');
            }
        } catch (e) {
            console.error("Modal show error:", e);
        }

        // Render Chart if ApexCharts exists
        if (typeof ApexCharts !== 'undefined') {
            if (window.accountChartInstance) {
                window.accountChartInstance.destroy();
            }
            const options = {
                series: series,
                chart: { type: 'donut', height: 280, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
                labels: labels, colors: colors, stroke: { show: false }, dataLabels: { enabled: false },
                legend: { position: 'bottom', horizontalAlign: 'center', fontSize: '12px' },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '80%',
                            labels: {
                                show: true,
                                total: {
                                    show: true, label: totalLabel, fontSize: '14px', color: '#64748b', fontWeight: 600,
                                    formatter: function () { return fmt(totalValue); }
                                },
                                value: {
                                    fontSize: totalValue > 1000000 ? '18px' : '22px', fontWeight: 700,
                                    color: '#0d1e3a', offsetY: 5,
                                    formatter: function (val) { return fmt(val); }
                                }
                            }
                        }
                    }
                },
                tooltip: { y: { formatter: function (val) { return fmt(val); } } }
            };
            window.accountChartInstance = new ApexCharts(document.querySelector("#accountChart"), options);
            window.accountChartInstance.render();
        } else {
            console.warn("ApexCharts not loaded yet, chart will follow...");
        }
    };
</script>
<script src="{{ asset('assets/front/js/apexcharts.js') }}"></script>
@endpush
