@extends('backend.layouts.app')
@section('title', __('Edit User Card'))
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Edit Card') }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.cards.update', $card->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">{{ __('User') }}</label>
                        <input type="text" class="form-control" value="{{ $card->user->full_name }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Card Number') }}</label>
                        <input type="text" class="form-control" value="{{ chunk_split($card->card_number, 4, ' ') }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="active" {{ $card->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $card->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="blocked" {{ $card->status == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="balance" class="form-label">{{ __('Balance') }}</label>
                        <input type="number" step="0.01" name="balance" id="balance" class="form-control" value="{{ $card->balance }}">
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('Update Card') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
