@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @can('manage_app_users')
            <remote-user-list :super-admin="true"></remote-user-list>
        @else
            <remote-user-list></remote-user-list>
        @endcan
    </div>
@endsection
