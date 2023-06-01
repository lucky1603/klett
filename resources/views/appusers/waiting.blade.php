@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column container">
        <user-list :show-mode="1"></user-list>
    </div>

@endsection
