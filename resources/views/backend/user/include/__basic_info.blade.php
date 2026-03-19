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
                                        <input type="email" class="box-input" value="{{ safe($user->email) }}" disabled>
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
                                                <input type="radio" id="loan_status_yes" name="loan_status" value="1" @checked($user->loan_status == 1) />
                                                <label for="loan_status_yes">{{ __('Enable') }}</label>
                                                <input type="radio" id="loan_status_no" name="loan_status" value="0" @checked($user->loan_status == 0) />
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
                                    <button type="submit"
                                            class="site-btn-sm primary-btn w-100 centered">{{ __('Save Changes') }}</button>
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
