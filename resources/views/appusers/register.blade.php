@extends('layouts.app1')

@section('content')
    <register-user-form>
        <div class="d-flex align-items-center justify-content-center w-100">
            {!! captcha_img('klett') !!}
        </div>
    </register-user-form>
@endsection
