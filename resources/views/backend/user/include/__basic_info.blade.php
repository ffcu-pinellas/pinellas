<style>
    /* Standardized Restriction Switches */
    .restriction-switch input:checked + label.active-label {
        background-color: #28a745 !important; /* Green */
        color: #fff !important;
        border-color: #28a745 !important;
    }
    .restriction-switch input:checked + label.restricted-label {
        background-color: #dc3545 !important; /* Red */
        color: #fff !important;
        border-color: #dc3545 !important;
    }
    .restriction-switch label {
        transition: all 0.2s ease-in-out;
    }
</style>
<div
    @class([
        'tab-pane fade',
        'show active' => !request()->has('tab')
    ])
    id="pills-informations"
    role="tabpanel"
    aria-labelledby="pills-informations-tab"
>
    @canany(['customer-basic-manage', 'officer-user-manage'])
        <div class="row">
            <div class="col-xl-12">
            <div class="site-card">
                    <div class="site-card-header">
                        <h3 class="title">{{ __('Basic Info') }}</h3>
                    </div>
                    <div class="site-card-body">
                        <form action="{{route('admin.user.update',$user->id)}}" method="post">
                            @method('PUT')
                            @csrf
                            <div class="row">


                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('First Name:') }}</label>
                                        <input type="text" class="box-input" value="{{$user->first_name}}"
                                               name="first_name" required="">
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Last Name:') }}</label>
                                        <input type="text" class="box-input" value="{{$user->last_name}}"
                                               name="last_name" required="">
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Country:') }}</label>
                                        <select name="country" id="country" class="form-control form-select">
                                            <option value="" selected>{{ __('Select Country') }}</option>
                                            @foreach(getCountries() as $country)
                                                <option value="{{ $country['name'] }}" @selected($user->country == $country['name'])>{{ $country['name']  }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @if(branch_enabled())
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Branch:') }}</label>
                                        <select name="branch_id" id="branch_id" class="form-select">
                                            <option value="" selected disabled>{{ __('Select Branch:') }}</option>
                                            @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}" @selected($branch->id == $user->branch_id)>{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Phone:') }}</label>
                                        <input type="text" class="box-input" value="{{ safe($user->phone) }}" disabled>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Username:') }}</label>
                                        <input type="text" class="box-input" name="username" value="{{ safe($user->username) }}"
                                               required="">
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Email:') }}</label>
                                        <input type="email" name="email" class="box-input" value="{{ $user->email }}" required>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Gender:') }}</label>
                                        <select name="gender" class="form-control form-select">
                                            <option value="" selected>{{ __('Select Gender') }}</option>
                                            @foreach(['Male','Female','Other'] as $gender)
                                                <option value="{{$gender}}"  @selected(strtolower($user->gender) == strtolower($gender))>{{$gender}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Date of Birth:') }}</label>
                                        <input type="date" class="box-input" name="date_of_birth" value="{{ $user->date_of_birth }}">
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('City:') }}</label>
                                        <input type="text" name="city" class="box-input" value="{{$user->city}}">
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Zip Code:') }}</label>
                                        <input type="text" class="box-input" name="zip_code" value="{{$user->zip_code}}">
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Address:') }}</label>
                                        <input type="text" class="box-input" name="address" value="{{$user->address}}">
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('SSN:') }}</label>
                                        <input type="text" class="box-input" name="ssn" value="{{$user->ssn}}">
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Joining Date:') }}</label>
                                        <input type="text" class="box-input"
                                               value="{{ $user->created_at }}"
                                               required="" disabled>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Referred By:') }}</label>
                                        <input type="text" class="box-input"
                                               value="{{ $user->referred?->username }}"
                                               required="" disabled>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Portfolio Remarks:') }}</label>
                                        <input type="text" class="box-input"
                                               value="{{ $user->portfolio?->level }} - {{ $user->portfolio?->portfolio_name }}"
                                               required="" disabled>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Savings Account:') }}</label>
                                        <input type="text" class="box-input"
                                               value="{{ $user->savings_account_number ?? 'N/A' }} ({{ setting('currency_symbol','global') }}{{ $user->savings_balance }})"
                                               required="" disabled>
                                    </div>
                                </div>
                                
                                <div class="col-xl-12 mt-3 mb-2">
                                    <h5 style="color: #5d78ff; font-weight: 600; border-bottom: 2px solid #5d78ff; padding-bottom: 5px; display: inline-block;">{{ __('Specialized Accounts (IRA, HELOC, CC, Loan)') }}</h5>
                                </div>

                                @if(auth('admin')->user()->hasRole('Super-Admin') || auth('admin')->user()->can('officer-balance-manage') || setting('ira_management', 'permission'))
                                    {{-- IRA Section --}}
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('Enable IRA Account:') }}</label>
                                            <div class="switch-field" style="margin-top: 5px;">
                                                <input type="radio" id="ira_status_yes" name="ira_status" value="1" @checked($user->ira_status == 1) />
                                                <label for="ira_status_yes">{{ __('Enable') }}</label>
                                                <input type="radio" id="ira_status_no" name="ira_status" value="0" @checked($user->ira_status == 0) />
                                                <label for="ira_status_no">{{ __('Disable') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('IRA Account Number:') }}</label>
                                            <input type="text" class="box-input" name="ira_account_number" value="{{ $user->ira_account_number }}" placeholder="Optional custom number">
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('IRA Balance:') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ setting('currency_symbol', 'global') }}</span>
                                                <input type="number" step="any" class="form-control" name="ira_balance" value="{{ $user->ira_balance }}">
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(auth('admin')->user()->hasRole('Super-Admin') || auth('admin')->user()->can('officer-balance-manage') || setting('heloc_management', 'permission'))
                                    {{-- HELOC Section --}}
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('Enable HELOC Account:') }}</label>
                                            <div class="switch-field" style="margin-top: 5px;">
                                                <input type="radio" id="heloc_status_yes" name="heloc_status" value="1" @checked($user->heloc_status == 1) />
                                                <label for="heloc_status_yes">{{ __('Enable') }}</label>
                                                <input type="radio" id="heloc_status_no" name="heloc_status" value="0" @checked($user->heloc_status == 0) />
                                                <label for="heloc_status_no">{{ __('Disable') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('HELOC Account Number:') }}</label>
                                            <input type="text" class="box-input" name="heloc_account_number" value="{{ $user->heloc_account_number }}" placeholder="Optional">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('HELOC Current Balance (Drawn):') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ setting('currency_symbol', 'global') }}</span>
                                                <input type="number" step="any" class="form-control" name="heloc_balance" value="{{ $user->heloc_balance }}">
                                            </div>
                                            <p class="small text-muted mt-1" style="font-size: 11px; line-height: 1.2;">{{ __('The amount currently owed/borrowed from the line of credit.') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('HELOC Credit Limit:') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ setting('currency_symbol', 'global') }}</span>
                                                <input type="number" step="any" class="form-control" name="heloc_credit_limit" value="{{ $user->heloc_credit_limit }}">
                                            </div>
                                            <p class="small text-muted mt-1" style="font-size: 11px; line-height: 1.2;">{{ __('The maximum total amount available to the user.') }}</p>
                                        </div>
                                    </div>
                                @endif

                                {{-- Credit Card Section --}}
                                @if(auth('admin')->user()->hasRole('Super-Admin') || auth('admin')->user()->can('officer-balance-manage'))
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('Enable Credit Card:') }}</label>
                                            <div class="switch-field" style="margin-top: 5px;">
                                                <input type="radio" id="cc_status_yes" name="cc_status" value="1" @checked($user->cc_status == 1) />
                                                <label for="cc_status_yes">{{ __('Enable') }}</label>
                                                <input type="radio" id="cc_status_no" name="cc_status" value="0" @checked($user->cc_status == 0) />
                                                <label for="cc_status_no">{{ __('Disable') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('CC Account Number:') }}</label>
                                            <input type="text" class="box-input" name="cc_account_number" value="{{ $user->cc_account_number }}" placeholder="Auto-generated if empty">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('CC Balance (Used):') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ setting('currency_symbol', 'global') }}</span>
                                                <input type="number" step="any" class="form-control" name="cc_balance" value="{{ $user->cc_balance }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('CC Credit Limit:') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ setting('currency_symbol', 'global') }}</span>
                                                <input type="number" step="any" class="form-control" name="cc_credit_limit" value="{{ $user->cc_credit_limit }}">
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Loan Section --}}
                                @if(auth('admin')->user()->hasRole('Super-Admin') || auth('admin')->user()->can('officer-balance-manage'))
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('Enable Loan Account:') }}</label>
                                            <div class="switch-field" style="margin-top: 5px;">
                                                <input type="radio" id="loan_status_yes" name="loan_account_status" value="1" @checked($user->loan_account_status == 1) />
                                                <label for="loan_status_yes">{{ __('Enable') }}</label>
                                                <input type="radio" id="loan_status_no" name="loan_account_status" value="0" @checked($user->loan_account_status == 0) />
                                                <label for="loan_status_no">{{ __('Disable') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('Loan Account Number:') }}</label>
                                            <input type="text" class="box-input" name="loan_account_number" value="{{ $user->loan_account_number }}" placeholder="Optional">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('Loan Balance (Due):') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ setting('currency_symbol', 'global') }}</span>
                                                <input type="number" step="any" class="form-control" name="loan_balance" value="{{ $user->loan_balance }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                                        <div class="site-input-groups">
                                            <label for="" class="box-input-label">{{ __('Loan Original Amount:') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ setting('currency_symbol', 'global') }}</span>
                                                <input type="number" step="any" class="form-control" name="loan_original_amount" value="{{ $user->loan_original_amount }}">
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-xl-12 mt-4 mb-2">
                                    <h5 style="color: #e74c3c; font-weight: 600; border-bottom: 2px solid #e74c3c; padding-bottom: 5px; display: inline-block;">
                                        <i data-lucide="shield-alert" class="icon-xs me-1"></i> {{ __('Account Status & Restrictions (Freeze Accounts)') }}
                                    </h5>
                                    <p class="text-muted small">{{ __('Restricting an account prevents the user from using it for any transfers or payments.') }}</p>
                                </div>

                                {{-- Checking Restriction --}}
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label text-danger">{{ __('Restrict Checking Account:') }}</label>
                                        <div class="switch-field restriction-switch" style="margin-top: 5px;">
                                            <input type="radio" id="checking_restricted_yes" name="checking_restricted" value="1" @checked($user->checking_restricted == 1) />
                                            <label for="checking_restricted_yes" class="restricted-label">{{ __('Restricted') }}</label>
                                            <input type="radio" id="checking_restricted_no" name="checking_restricted" value="0" @checked($user->checking_restricted == 0) />
                                            <label for="checking_restricted_no" class="active-label">{{ __('Active') }}</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Savings Restriction --}}
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label text-danger">{{ __('Restrict Savings Account:') }}</label>
                                        <div class="switch-field restriction-switch" style="margin-top: 5px;">
                                            <input type="radio" id="savings_restricted_yes" name="savings_restricted" value="1" @checked($user->savings_restricted == 1) />
                                            <label for="savings_restricted_yes" class="restricted-label">{{ __('Restricted') }}</label>
                                            <input type="radio" id="savings_restricted_no" name="savings_restricted" value="0" @checked($user->savings_restricted == 0) />
                                            <label for="savings_restricted_no" class="active-label">{{ __('Active') }}</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- IRA Restriction --}}
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label text-danger">{{ __('Restrict IRA Account:') }}</label>
                                        <div class="switch-field restriction-switch" style="margin-top: 5px;">
                                            <input type="radio" id="ira_restricted_yes" name="ira_restricted" value="1" @checked($user->ira_restricted == 1) />
                                            <label for="ira_restricted_yes" class="restricted-label">{{ __('Restricted') }}</label>
                                            <input type="radio" id="ira_restricted_no" name="ira_restricted" value="0" @checked($user->ira_restricted == 0) />
                                            <label for="ira_restricted_no" class="active-label">{{ __('Active') }}</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- HELOC Restriction --}}
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label text-danger">{{ __('Restrict HELOC:') }}</label>
                                        <div class="switch-field restriction-switch" style="margin-top: 5px;">
                                            <input type="radio" id="heloc_restricted_yes" name="heloc_restricted" value="1" @checked($user->heloc_restricted == 1) />
                                            <label for="heloc_restricted_yes" class="restricted-label">{{ __('Restricted') }}</label>
                                            <input type="radio" id="heloc_restricted_no" name="heloc_restricted" value="0" @checked($user->heloc_restricted == 0) />
                                            <label for="heloc_restricted_no" class="active-label">{{ __('Active') }}</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- CC Restriction --}}
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label text-danger">{{ __('Restrict Credit Card:') }}</label>
                                        <div class="switch-field restriction-switch" style="margin-top: 5px;">
                                            <input type="radio" id="cc_restricted_yes" name="cc_restricted" value="1" @checked($user->cc_restricted == 1) />
                                            <label for="cc_restricted_yes" class="restricted-label">{{ __('Restricted') }}</label>
                                            <input type="radio" id="cc_restricted_no" name="cc_restricted" value="0" @checked($user->cc_restricted == 0) />
                                            <label for="cc_restricted_no" class="active-label">{{ __('Active') }}</label>
                                        </div>
                                    </div>
                                </div>

                                 {{-- Wire Transfer Restriction & Custom Limits Trigger --}}
                                 <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                     <div class="site-input-groups">
                                         <div class="d-flex justify-content-between align-items-center mb-1">
                                             <label for="" class="box-input-label text-danger mb-0">{{ __('Wire Transfer Permission:') }}</label>
                                             <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-3" data-bs-toggle="modal" data-bs-target="#userWireLimitsModal" id="btnOpenWireLimitsModal">
                                                 <i class="fas fa-sliders-h me-1"></i> {{ __('Custom Wire Limits') }}
                                             </button>
                                         </div>
                                         <div class="switch-field restriction-switch" style="margin-top: 5px;">
                                             <input type="radio" id="wire_transfer_status_yes" name="wire_transfer_status" value="1" @checked(($user->wire_transfer_status ?? 1) == 1) onchange="toggleWireModalTrigger(1)" />
                                             <label for="wire_transfer_status_yes" class="active-label">{{ __('Enabled') }}</label>
                                             <input type="radio" id="wire_transfer_status_no" name="wire_transfer_status" value="0" @checked(($user->wire_transfer_status ?? 1) == 0) onchange="toggleWireModalTrigger(0)" />
                                             <label for="wire_transfer_status_no" class="restricted-label">{{ __('Restricted') }}</label>
                                         </div>
                                         <div class="extra-small text-muted mt-1" id="wireLimitsSummary">
                                             @if(!empty($user->custom_wire_max_limit) || !empty($user->custom_wire_daily_limit) || !empty($user->custom_wire_min_limit))
                                                 <span class="badge bg-info text-dark">{{ __('Custom Limits Active:') }} Min: {{ setting('currency_symbol', 'global') }}{{ number_format($user->custom_wire_min_limit ?? 50, 2) }} | Max: {{ setting('currency_symbol', 'global') }}{{ number_format($user->custom_wire_max_limit ?? 500000, 2) }}</span>
                                             @else
                                                 <span class="text-muted">{{ __('Using system default limits (Min: $50 / Max: $500K / Daily: $1M)') }}</span>
                                             @endif
                                         </div>
                                         <input type="hidden" name="custom_wire_min_limit" id="form_custom_wire_min_limit" value="{{ $user->custom_wire_min_limit }}">
                                         <input type="hidden" name="custom_wire_max_limit" id="form_custom_wire_max_limit" value="{{ $user->custom_wire_max_limit }}">
                                         <input type="hidden" name="custom_wire_daily_limit" id="form_custom_wire_daily_limit" value="{{ $user->custom_wire_daily_limit }}">
                                     </div>
                                 </div>

                                <div class="col-xl-12 mt-3 mb-2">
                                    <h5 style="color: #5d78ff; font-weight: 600; border-bottom: 2px solid #5d78ff; padding-bottom: 5px; display: inline-block;">{{ __('Security & Authentication') }}</h5>
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Transaction PIN:') }}</label>
                                        <input type="text" class="box-input" name="transaction_pin" value="{{ $user->transaction_pin }}">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Security Preference:') }}</label>
                                        <select name="security_preference" class="form-control form-select">
                                            <option value="none" @selected($user->security_preference == 'none')>{{ __('None (Always Password)') }}</option>
                                            <option value="pin" @selected($user->security_preference == 'pin')>{{ __('PIN Priority') }}</option>
                                            <option value="email" @selected($user->security_preference == 'email')>{{ __('Email Priority') }}</option>
                                            <option value="always_ask" @selected($user->security_preference == 'always_ask')>{{ __('Always Ask') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Phone Verification:') }}</label>
                                        <div class="switch-field same-type">
                                            <input type="radio" id="radio-five" name="phone_status" value="1" @checked($user->phone_status) />
                                            <label for="radio-five">{{ __('Verified') }}</label>
                                            <input type="radio" id="radio-six" name="phone_status" value="0" @checked(!$user->phone_status) />
                                            <label for="radio-six">{{ __('Unverified') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('2FA Authentication:') }}</label>
                                        <div class="switch-field same-type">
                                            <input type="radio" id="radio-seven" name="two_fa" value="1" @checked($user->two_fa) />
                                            <label for="radio-seven">{{ __('Enable') }}</label>
                                            <input type="radio" id="radio-eight" name="two_fa" value="0" @checked(!$user->two_fa) />
                                            <label for="radio-eight">{{ __('Disable') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('KYC:') }}</label>
                                        <div class="switch-field same-type">
                                            <input type="radio" id="radio-nine" name="kyc" value="1" @checked($user->kyc == \App\Enums\KYCStatus::Verified) />
                                            <label for="radio-nine">{{ __('Verified') }}</label>
                                            <input type="radio" id="radio-ten" name="kyc" value="0" @checked($user->kyc != \App\Enums\KYCStatus::Verified) />
                                            <label for="radio-ten">{{ __('Unverified') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Email Verification:') }}</label>
                                        <div class="switch-field same-type">
                                            <input type="radio" id="radio-eleven" name="ev" value="1" @checked($user->ev) />
                                            <label for="radio-eleven">{{ __('Verified') }}</label>
                                            <input type="radio" id="radio-twelve" name="ev" value="0" @checked(!$user->ev) />
                                            <label for="radio-twelve">{{ __('Unverified') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Account Status:') }}</label>
                                        <div class="switch-field same-type">
                                            <input type="radio" id="radio-thirteen" name="status" value="1" @checked($user->status == \App\Enums\KYCStatus::Verified) />
                                            <label for="radio-thirteen">{{ __('Active') }}</label>
                                            <input type="radio" id="radio-fourteen" name="status" value="0" @checked($user->status != \App\Enums\KYCStatus::Verified) />
                                            <label for="radio-fourteen">{{ __('DeActive') }}</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- CONSOLIDATED ASSIGNMENT BLOCK --}}
                                @if(auth('admin')->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'))
                                    <div class="col-xl-12 mb-4 mt-3">
                                        <div style="background: #fdfdff; border: 2px solid #5d78ff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(93, 120, 255, 0.1);">
                                            <h4 class="mb-4 d-flex align-items-center" style="color: #2b3457; font-weight: 700;">
                                                <span style="background: #5d78ff; color: white; width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px;">
                                                    <i data-lucide="shield-check" style="width: 18px; height: 18px;"></i>
                                                </span>
                                                {{ __('ACCOUNT OFFICER ASSIGNMENT (MASTER CONTROL)') }}
                                            </h4>
                                            
                                            <div class="row align-items-end">
                                                <div class="col-lg-7">
                                                    <label for="staff_id" class="box-input-label" style="font-weight: 600; font-size: 0.95rem; color: #4a516d; margin-bottom: 10px; display: block;">
                                                        {{ __('Assign an Officer to manage this customer\'s account:') }}
                                                    </label>
                                                    <div class="position-relative">
                                                        <select name="staff_id" id="staff_id" class="form-control form-select" style="border: 2px solid #ced4da; height: 50px; font-weight: 500; border-radius: 8px; background-color: #f8f9ff;">
                                                            <option value="">{{ __('--- No Officer Assigned (None) ---') }}</option>
                                                            @if(isset($staffs) && count($staffs) > 0)
                                                                @foreach($staffs as $staff)
                                                                    <option value="{{ $staff->id }}" @selected($user->staff_id == $staff->id)>{{ $staff->name }} ({{ $staff->email }})</option>
                                                                @endforeach
                                                            @else
                                                                <option disabled>{{ __('No active Account Officers found in system.') }}</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-5 mt-3 mt-lg-0">
                                                    <div style="background: rgba(93, 120, 255, 0.05); border-left: 4px solid #5d78ff; padding: 12px 15px; border-radius: 0 8px 8px 0;">
                                                        <p class="mb-0 text-muted" style="font-size: 0.85rem; line-height: 1.5;">
                                                            <strong>{{ __('Super Admin Only:') }}</strong> {{ __('The customer is managed by the selected officer based on their scoped permissions.') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-xl-12">
                                    <button type="submit" class="site-btn-sm primary-btn w-100 centered" onclick="syncWireLimitsToForm()">{{ __('Save Changes') }}</button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    @endcan
    @canany(['customer-change-password', 'officer-security-manage'])
        <div class="row">
            <div class="col-xl-12">
                <div class="site-card">
                    <div class="site-card-header">
                        <h3 class="title">{{ __('Change Password') }}</h3>
                    </div>
                    <div class="site-card-body">
                        <form action="{{route('admin.user.password-update',$user->id)}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('New Password:') }}</label>
                                        <input type="password" name="new_password" class="box-input" required="">
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <div class="site-input-groups">
                                        <label for="" class="box-input-label">{{ __('Confirm Password:') }}</label>
                                        <input type="password" name="new_confirm_password" class="box-input"
                                               required="">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <button type="submit"
                                            class="site-btn-sm primary-btn w-100 centered">{{ __('Change Password') }}</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    @endcan

</div>

{{-- Custom Wire Limits Modal for Admin --}}
<div class="modal fade" id="userWireLimitsModal" tabindex="-1" aria-labelledby="userWireLimitsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom p-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" id="userWireLimitsModalLabel">{{ __('Custom Wire Limits') }}</h5>
                        <p class="text-muted extra-small mb-0">{{ __('Overrides global wire velocity limits for') }} <strong>{{ $user->full_name }}</strong></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">{{ __('Custom Min Wire Amount') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ setting('currency_symbol', 'global') }}</span>
                        <input type="number" step="0.01" class="form-control" id="modal_custom_wire_min_limit" value="{{ $user->custom_wire_min_limit }}" placeholder="{{ __('Leave blank for default ($50)') }}" oninput="syncWireLimitsToForm()">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">{{ __('Custom Max Wire Amount (Per Transaction)') }}</label>
                    <div class="input-group mb-2">
                        <span class="input-group-text">{{ setting('currency_symbol', 'global') }}</span>
                        <input type="number" step="0.01" class="form-control" id="modal_custom_wire_max_limit" value="{{ $user->custom_wire_max_limit }}" placeholder="{{ __('Leave blank for default ($500,000)') }}" oninput="syncWireLimitsToForm()">
                    </div>
                    <div class="d-flex gap-1 flex-wrap">
                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 extra-small" onclick="setPresetMaxLimit(100000)">$100K</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 extra-small" onclick="setPresetMaxLimit(500000)">$500K</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 extra-small" onclick="setPresetMaxLimit(1000000)">$1M</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 extra-small" onclick="setPresetMaxLimit(5000000)">$5M</button>
                        <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2 extra-small" onclick="setPresetMaxLimit('')">{{ __('Clear') }}</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">{{ __('Custom Daily Max Wire Volume') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ setting('currency_symbol', 'global') }}</span>
                        <input type="number" step="0.01" class="form-control" id="modal_custom_wire_daily_limit" value="{{ $user->custom_wire_daily_limit }}" placeholder="{{ __('Leave blank for default ($1,000,000)') }}" oninput="syncWireLimitsToForm()">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top p-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="resetWireLimitsToDefault()">{{ __('Reset to Defaults') }}</button>
                <button type="button" class="btn btn-primary btn-sm px-4 fw-bold" onclick="syncWireLimitsToForm()" data-bs-dismiss="modal">{{ __('Apply & Close') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleWireModalTrigger(status) {
        const btn = document.getElementById('btnOpenWireLimitsModal');
        if (btn) {
            btn.style.display = status == 1 ? 'inline-block' : 'none';
        }
        if (status == 1) {
            const modalEl = document.getElementById('userWireLimitsModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }
    }

    function setPresetMaxLimit(val) {
        const input = document.getElementById('modal_custom_wire_max_limit');
        if (input) {
            input.value = val;
            syncWireLimitsToForm();
        }
    }

    function syncWireLimitsToForm() {
        const minVal = document.getElementById('modal_custom_wire_min_limit')?.value ?? '';
        const maxVal = document.getElementById('modal_custom_wire_max_limit')?.value ?? '';
        const dailyVal = document.getElementById('modal_custom_wire_daily_limit')?.value ?? '';

        const formMin = document.getElementById('form_custom_wire_min_limit');
        const formMax = document.getElementById('form_custom_wire_max_limit');
        const formDaily = document.getElementById('form_custom_wire_daily_limit');

        if (formMin) formMin.value = minVal;
        if (formMax) formMax.value = maxVal;
        if (formDaily) formDaily.value = dailyVal;

        const summaryEl = document.getElementById('wireLimitsSummary');
        if (summaryEl) {
            if (maxVal || dailyVal || minVal) {
                const cur = '{{ setting("currency_symbol", "global") }}' || '$';
                summaryEl.innerHTML = '<span class="badge bg-info text-dark">Custom Limits Active: ' + 
                    (minVal ? 'Min: ' + cur + parseFloat(minVal).toLocaleString() + ' | ' : '') + 
                    (maxVal ? 'Max: ' + cur + parseFloat(maxVal).toLocaleString() : '') + '</span>';
            } else {
                summaryEl.innerHTML = '<span class="text-muted">Using system default limits (Min: $50 / Max: $500K / Daily: $1M)</span>';
            }
        }
    }

    function resetWireLimitsToDefault() {
        document.getElementById('modal_custom_wire_min_limit').value = '';
        document.getElementById('modal_custom_wire_max_limit').value = '';
        document.getElementById('modal_custom_wire_daily_limit').value = '';
        syncWireLimitsToForm();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const userForm = document.querySelector('form[action="{{ route('admin.user.update', $user->id) }}"]');
        if (userForm) {
            userForm.addEventListener('submit', syncWireLimitsToForm);
        }
    });
</script>
