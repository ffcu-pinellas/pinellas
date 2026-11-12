@extends('frontend::layouts.user')

@section('title')
    {{ __('Documents & E-Signatures') }}
@endsection

@section('content')
<div class="container-fluid py-3">
    <div class="row mb-4">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="h4 fw-bold mb-1">{{ __('Member Documents & E-Signatures') }}</h2>
                <p class="text-muted small mb-0">{{ __('Review disclosures, agreements, and official credit union records requiring your electronic signature.') }}</p>
            </div>
            @if($pendingCount > 0)
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                    <i class="fas fa-pen-nib me-1"></i> {{ $pendingCount }} {{ __('Awaiting Your Signature') }}
                </span>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="banno-card p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Document Title') }}</th>
                                <th>{{ __('Issued') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Expires / Signed') }}</th>
                                <th class="text-end">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($signatures as $sig)
                                @php
                                    $docTitle = data_get($sig->metadata, 'title') ?? ($sig->documentHistory?->title ?? 'Official Member Agreement');
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $docTitle }}</td>
                                    <td class="text-muted small">{{ $sig->created_at?->format('M d, Y') }}</td>
                                    <td>
                                        @if($sig->status === 'signed')
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> {{ __('Executed & Signed') }}</span>
                                        @elseif($sig->status === 'pending')
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> {{ __('Action Required') }}</span>
                                        @elseif($sig->status === 'declined')
                                            <span class="badge bg-danger">{{ __('Declined') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Expired') }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        @if($sig->status === 'signed' && $sig->signed_at)
                                            {{ __('Signed') }} {{ $sig->signed_at->format('M d, Y H:i') }} UTC
                                        @elseif($sig->expires_at)
                                            {{ __('Due by') }} {{ $sig->expires_at->format('M d, Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($sig->status === 'pending')
                                            <a href="{{ route('user.documents.sign.show', $sig->external_signature_id) }}" class="btn btn-sm btn-primary fw-semibold">
                                                <i class="fas fa-signature me-1"></i> {{ __('Review & Sign') }}
                                            </a>
                                        @else
                                            <a href="{{ route('user.documents.sign.show', $sig->external_signature_id) }}" class="btn btn-sm btn-outline-secondary me-1">
                                                {{ __('View') }}
                                            </a>
                                            <a href="{{ route('user.documents.download', $sig->external_signature_id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-pdf me-1"></i> {{ __('Download PDF') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-2x mb-2 opacity-50"></i>
                                        <div class="fw-semibold">{{ __('No electronic signature requests on file yet.') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($signatures->hasPages())
                    <div class="mt-3">
                        {{ $signatures->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

