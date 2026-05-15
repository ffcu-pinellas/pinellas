@extends('backend.layouts.app')
@section('title')
    {{ __('Document Templates') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('Document Templates') }}</h2>
                            <a href="{{ route('admin.document-template.create') }}" class="site-btn primary-btn"><i class="ant-plus"></i>{{ __('Create Template') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="site-card">
                        <div class="site-card-body">
                            <form action="{{ route('admin.document-template.index') }}" method="get">
                                <div class="row">
                                    <div class="col-xl-4 col-md-6">
                                        <div class="input-box">
                                            <label for="category">{{ __('Category') }}</label>
                                            <select class="form-select" name="category" id="category">
                                                <option value="">{{ __('All Categories') }}</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $category)) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-md-6">
                                        <div class="input-box">
                                            <label for="search">{{ __('Search Name') }}</label>
                                            <input type="text" class="form-control" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Search templates...') }}">
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-md-12 d-flex align-items-end">
                                        <button type="submit" class="site-btn primary-btn w-100 mb-3">{{ __('Filter Results') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="site-card mt-4">
                        <div class="site-card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Created By') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created At') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($templates as $template)
                                        <tr>
                                            <td>
                                                <strong>{{ $template->name }}</strong>
                                                @if($template->description)
                                                    <br><small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ ucwords(str_replace('_', ' ', $template->category)) }}</span>
                                            </td>
                                            <td>{{ $template->creator ? $template->creator->name : __('System') }}</td>
                                            <td>
                                                @if($template->is_active)
                                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $template->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.document-generator.index', ['template_id' => $template->id]) }}" class="btn btn-sm btn-success" title="{{ __('Use Template') }}">
                                                        <i class="ant-file-add"></i> {{ __('Use') }}
                                                    </a>
                                                    <a href="{{ route('admin.document-template.edit', $template->id) }}" class="btn btn-sm btn-primary" title="{{ __('Edit') }}">
                                                        <i class="ant-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.document-template.destroy', $template->id) }}" method="post" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger delete-confirm" title="{{ __('Delete') }}">
                                                            <i class="ant-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">{{ __('No templates found.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mt-3">
                                {{ $templates->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.delete-confirm').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
