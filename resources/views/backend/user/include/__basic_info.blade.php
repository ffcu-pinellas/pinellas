<div
    @class([
        'tab-pane fade',
        'show active' => !request()->has('tab')
    ])
    id="pills-informations"
    role="tabpanel"
    aria-labelledby="pills-informations-tab"
>
    @can('customer-basic-manage')
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



                                <div class="col-xl-12">
                                    <button type="submit"
                                            class="site-btn-sm primary-btn w-100 centered">{{ __('Save Changes') }}</button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                <div class="site-card mt-4">
                    <div class="site-card-header">
                        <h3 class="title">{{ __('Account Officer Assignment') }}</h3>
                    </div>
                    <div class="site-card-body">
                        <form action="{{route('admin.user.update',$user->id)}}" method="post">
                            @method('PUT')
                            @csrf
                            <div style="background: #fdfdff; border: 1px solid #e1e4f3; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                                <h4 class="mb-4 d-flex align-items-center" style="color: #2b3457; font-weight: 700;">
                                    <span style="background: #5d78ff; color: white; width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px;">
                                        <i data-lucide="shield-check" style="width: 18px; height: 18px;"></i>
                                    </span>
                                    {{ __('ASSIGN THIS CUSTOMER TO AN OFFICER') }}
                                </h4>
                                
                                <div class="row align-items-end">
                                    <div class="col-lg-7">
                                        <label for="staff_id" class="box-input-label" style="font-weight: 600; font-size: 0.95rem; color: #4a516d; margin-bottom: 10px; display: block;">
                                            {{ __('Select an Officer to manage this customer\'s account:') }}
                                        </label>
                                        <div class="position-relative">
                                            <select name="staff_id" id="staff_id" class="form-control form-select" style="border: 1px solid #ced4da; height: 50px; font-weight: 500; border-radius: 8px;">
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
                                        <button type="submit" class="site-btn-sm primary-btn w-100" style="height: 50px;">
                                            <i data-lucide="check"></i> {{ __('Update Manager / Assignment') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-3" style="background: rgba(93, 120, 255, 0.05); border-left: 4px solid #5d78ff; padding: 12px 15px; border-radius: 0 8px 8px 0;">
                                    <p class="mb-0 text-muted" style="font-size: 0.85rem; line-height: 1.5;">
                                        <strong>{{ __('Security Policy:') }}</strong> {{ __('Only Super Admins can re-assign customers. The assigned officer will gain management permissions once assigned.') }}
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    @endcan
    @can('customer-change-password')
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
