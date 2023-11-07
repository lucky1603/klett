@extends('layouts.app')

@section('content')
    <a href="{{route('anonimous.requesteditprofile', ['email' => 'frunzic@yahoo.com']) }}" class="btn btn-primary" role="button">
        Pritisni me
    </a>
@endsection
