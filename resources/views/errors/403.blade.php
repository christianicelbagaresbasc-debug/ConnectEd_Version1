@extends('errors.layout')

@section('title', '403 Forbidden - Access Restricted')
@section('code', '403')
@section('heading', 'Access Restricted')
@section('icon-bg', 'bg-warning/10 text-warning')

@section('icon')
    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
    </svg>
@endsection

@section('message')
    You do not possess the required institutional permissions or role assignment to view this page. If you believe this is an error, please contact the OJT Coordinator.
@endsection
