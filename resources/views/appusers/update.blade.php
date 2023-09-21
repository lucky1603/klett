@extends('layouts.app1')

@section('content')
    <update-user-form user-id="{{ $user }}">
        <div class="d-flex align-items-center justify-content-center w-100">
            {!! captcha_img('klett') !!}
        </div>
    </update-user-form>
@endsection
