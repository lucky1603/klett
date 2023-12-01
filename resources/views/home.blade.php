@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <dashboard is-admin="{{ $is_admin ? "true" : "false" }}"></dashboard>
</div>
@endsection
