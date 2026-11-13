<?php

namespace App\Http\Requests\Auth;

use App\Rules\Recaptcha;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->has('username') && ! $this->filled('email')) {
            $this->merge(['email' => $this->input('username')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required_without:username', 'nullable', 'string'],
            'username' => ['required_without:email', 'nullable', 'string'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => Rule::requiredIf(plugin_active('Google reCaptcha')), new Recaptcha,
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $identifier = $this->get('email') ?? $this->get('username');

        $credentials = ['email' => $identifier, 'password' => $this->get('password')];
        $type = 'email';

        if (! $this->isEmail($identifier)) {
            $credentials = [
                'username' => $identifier,
                'password' => $this->get('password'),
            ];
            $type = 'username';
        }

        $this->ensureIsNotRateLimited($type);

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
                'username' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Validate if provided parameter is valid email.
     *
     * @return bool
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function isEmail($param)
    {
        $factory = $this->container->make(ValidationFactory::class);

        return ! $factory->make(
            ['email' => $param],
            ['email' => 'email']
        )->fails();
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited($type)
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            $type => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
