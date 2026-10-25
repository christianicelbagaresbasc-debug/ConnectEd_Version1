@extends('errors.layout')

@section('title', '404 Not Found')
@section('code', '404')
@section('heading', 'Page Not Found')
@section('icon-bg', 'bg-primary/10 text-primary')

@section('icon')
    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
    </svg>
@endsection

@section('message')
    The requested resource, record, or URL could not be located in the ConnectEd system. It may have been archived or moved.
@endsection
