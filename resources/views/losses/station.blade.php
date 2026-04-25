@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Losses for {{ $station->name }}</h1>

    <div class="mb-3">
        <a href="{{ route('losses.create') }}" class="btn btn-primary">
            Record New Loss
        </a>
        <a href="{{ route('losses.index') }}" class="btn btn-secondary">
            View All Losses
        </a>
        <a href="{{ route('stations.losses', $station) }}" class="btn btn-info">
            View Loss Reports
        </a>
    </div>

</div>
@endsection
