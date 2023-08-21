@extends('layouts.app3')

@section('content')

<div class="d-flex flex-column align-items-center justify-content-center h-100">
    <div class="d-flex align-items-center justify-content-center" >
        <img src="/images/klettlogobig.png" class="w-50">
    </div>
    <div class="d-flex align-items-center justify-content-center flex-column w-100 my-auto">
        <h5 class="mb-4">{{ __('Login') }}</h5>
        <!-- form -->
        <form action="{{ route('login') }}" method="POST" class="w-75">
            @csrf
            <div class="form-group">
                <label for="email">{{ __('Email Address') }}</label>
                <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" placeholder="{{ __('Enter your email') }}" value="{{ old('email') }}">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">{{ __('Password') }}</label>
                <input class="form-control @error('password') is-invalid @enderror" type="password" id="password" name="password" placeholder="{{ __('Enter your password') }}" value="{{ old('email') }}">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group row mb-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                        <label class="form-check-label" for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group mb-0 text-center">
                <button class="btn btn-primary btn-block" type="submit"><i class="mdi mdi-login mr-1"></i>{{ __('Log in') }}</button>
                @if (Route::has('password.request'))
                    <a class="btn btn-link" href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>





@endsection
