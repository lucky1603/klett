@extends('layouts.app')

@section('content')
    <div class="container">
        <h5 class="text-center">{{ __("Schools List") }}</h5>
        <school-table></school-table>
    </div>

@endsection

