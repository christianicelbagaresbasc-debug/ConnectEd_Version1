@extends('errors.layout')

@section('title', '419 Page Expired')
@section('code', '419')
@section('heading', 'Session Expired')
@section('icon-bg', 'bg-warning/10 text-warning')

@section('icon')
    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>
@endsection

@section('message')
    Your security session or form submission token has expired due to inactivity. Please refresh the page and try submitting your request again.
@endsection
