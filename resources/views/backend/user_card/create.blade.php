@extends('backend.layouts.app')
@section('title', __('Create User Card'))
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Create New Card') }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.cards.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="user_id" class="form-label">{{ __('Select User') }}</label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            <option value="">{{ __('Select User') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">{{ __('Card Type') }}</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="Visa">Visa</option>
                            <option value="Mastercard">Mastercard</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="balance" class="form-label">{{ __('Initial Balance') }}</label>
                        <input type="number" step="0.01" name="balance" id="balance" class="form-control" value="0">
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('Create Card') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
