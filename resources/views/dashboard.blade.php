@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="dashboard-container">
        @include('components.dashboard.header')

        <!-- Statistics Cards -->
        <div class="row" id="row">
            @include('components.dashboard.stat-card')
        </div>

        <!-- Quick Actions -->
        @include('components.dashboard.quick-actions')

        <!-- Payment Overview Section -->
        <div class="row">
            @include('components.dashboard.upcoming-payments')
            @include('components.dashboard.recent-payments')
        </div>

        <!-- Notification Stats -->
        @include('components.dashboard.notification-stats')

        <!-- Stations Summary -->
        @include('components.dashboard.stations-summary')

        <!-- Expiring & Due Items -->
        <div class="row">
            @include('components.dashboard.expiring-airtime')
            @include('components.dashboard.due-internet')
        </div>
    </div>
@endsection

@section('styles')
    @include('components.dashboard.styles')
@endsection
