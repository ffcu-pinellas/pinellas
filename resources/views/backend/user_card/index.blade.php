@extends('backend.layouts.app')
@section('title', __('User Cards'))
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('User Cards List') }}</h4>
                <a href="{{ route('admin.cards.create') }}" class="btn btn-primary float-end"><i class="fas fa-plus"></i> {{ __('Add New Card') }}</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Card Number') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Expiry') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cards as $card)
                            <tr>
                                <td>{{ $card->user->full_name ?? 'N/A' }}</td>
                                <td>{{ chunk_split($card->card_number, 4, ' ') }}</td>
                                <td>{{ $card->type }}</td>
                                <td>{{ $card->expiry_month }}/{{ substr($card->expiry_year, -2) }}</td>
                                <td>
                                    @if($card->status == 'active')
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @elseif($card->status == 'inactive')
                                        <span class="badge bg-warning">{{ __('Inactive') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Blocked') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.cards.edit', $card->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.cards.destroy', $card->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('No cards found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $cards->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
