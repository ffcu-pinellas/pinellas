<div class="modal fade" id="editBox" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0">{{ __('Edit Recipient') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.fund_transfer.beneficiary.update') }}" method="POST" onsubmit="event.preventDefault(); SecurityGate.gate(this);">
                @csrf
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="small text-muted text-uppercase fw-bold mb-2 d-block">{{ __('Financial Institution') }}</label>
                        <select class="form-select border-2 rounded-3 p-2 fw-600" name="bank_id" id="edit_bank_name" required>
                            <option value="0">{{ __('Own Bank') }}</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold mb-2 d-block">{{ __('Account Number') }}</label>
                        <input type="text" class="form-control border-2 rounded-3 p-2 fw-600" name="account_number" id="edit_account_number" required placeholder="Enter number...">
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold mb-2 d-block">{{ __('Full Name on Account') }}</label>
                        <input type="text" class="form-control border-2 rounded-3 p-2 fw-600" name="account_name" id="edit_account_name" required placeholder="John Doe...">
                    </div>

                    @if (setting('multi_branch', 'permission'))
                        <div class="mb-3">
                            <label class="small text-muted text-uppercase fw-bold mb-2 d-block">{{ __('Branch Name') }}</label>
                            <input type="text" class="form-control border-2 rounded-3 p-2 fw-600" name="branch_name" id="edit_branch_name" placeholder="Main Branch...">
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold mb-2 d-block">{{ __('Nickname') }}</label>
                        <input type="text" class="form-control border-2 rounded-3 p-2 fw-600" name="nick_name" id="edit_nick_name" placeholder="E.g. Mum's Account">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-3 fw-bold shadow-sm">{{ __('Save Changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

