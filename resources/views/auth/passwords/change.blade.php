@extends('layouts.app')

@section('content')
    <change-password-form user-token={{ $token }}></change-password-form>
@endsection
