@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-center h-100">
        <a href="{{route('anonimous.requesteditprofile', ['email' => $email]) }}" class="btn btn-primary" role="button">
            Pritisni me
        </a>
    </div>
</div>

@endsection
