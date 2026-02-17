<div class="modal fade" id="deleteBox" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0">{{ __('Delete Recipient') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle mb-3" style="width: 64px; height: 64px;">
                        <i class="fas fa-exclamation-triangle fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Are you absolutely sure?</h5>
                    <p class="text-muted">You are about to remove this recipient. This action cannot be undone and they will be removed from your list.</p>
                </div>

                <form action="{{ route('user.fund_transfer.beneficiary.delete') }}" method="POST" id="dltForm">
                    @csrf
                    <input type="hidden" name="id" id="dltId" value="">
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger rounded-pill py-3 fw-bold shadow-sm">
                            Yes, delete recipient
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill py-3 fw-bold" data-bs-dismiss="modal">
                            No, keep them
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

