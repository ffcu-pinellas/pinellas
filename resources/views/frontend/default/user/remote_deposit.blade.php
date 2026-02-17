@extends('frontend::layouts.user')

@section('title')
{{ __('Remote Deposit') }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-md-10 mx-auto">
        <div class="site-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0">Remote Check Deposit</h5>
                <i class="fas fa-camera text-primary fs-4"></i>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info border-0 d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-info-circle fs-4 me-3"></i>
                    <div>
                        <strong>Tip:</strong> Endorse the back of your check with "For Mobile Deposit Only to Pinellas FCU".
                    </div>
                </div>
                <div class="site-card-body">
                    <form action="{{ route('user.remote_deposit.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Select Account</label>
                                <select name="account_id" class="form-select" required>
                                    <option value="checking">Checking - ****{{ substr(auth()->user()->account_number, -4) }}</option>
                                    @php
                                        $savingsAccounts = \App\Models\SavingsAccount::where('user_id', auth()->id())->get();
                                    @endphp
                                    @foreach($savingsAccounts as $savings)
                                        <option value="savings_{{ $savings->id }}">Savings - ****{{ substr($savings->account_number, -4) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Deposit Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold d-block mb-2">Front of Check</label>
                                <div class="border rounded p-3 text-center bg-light" style="border-style: dashed !important; border-width: 2px;">
                                    <label class="w-100 h-100 d-block cursor-pointer">
                                        <i class="fas fa-camera fs-1 text-muted mb-3"></i>
                                        <span class="d-block fw-bold text-primary">Capture Front</span>
                                        <input type="file" name="front_image" class="d-none" accept="image/*" capture="environment" required>
                                    </label>
                                </div>
                            </div>
                             <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold d-block mb-2">Back of Check</label>
                                <div class="border rounded p-3 text-center bg-light" style="border-style: dashed !important; border-width: 2px;">
                                    <label class="w-100 h-100 d-block cursor-pointer">
                                        <i class="fas fa-signature fs-1 text-muted mb-3"></i>
                                        <span class="d-block fw-bold text-primary">Capture Back</span>
                                        <input type="file" name="back_image" class="d-none" accept="image/*" capture="environment" required>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.querySelectorAll('input[type="file"]').forEach(input => {
                                input.addEventListener('change', function() {
                                    if (this.files && this.files[0]) {
                                        let label = this.closest('label').querySelector('span');
                                        label.textContent = 'Selected: ' + this.files[0].name;
                                        label.classList.remove('text-primary');
                                        label.classList.add('text-success');
                                        this.closest('label').querySelector('i').classList.replace('text-muted', 'text-success');
                                    }
                                });
                            });
                        </script>

                        <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info small">
                            <i class="fas fa-info-circle me-1"></i> Funds are normally available within 1-2 business days. Please retain the check until funds post to your account.
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Submit Deposit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```
