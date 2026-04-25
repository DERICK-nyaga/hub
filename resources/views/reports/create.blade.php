@extends('layouts.app')

@section('content')
    <h1 class="mb-0">Create New Report</h1>
    @include('reports.form', [
        'action' => route('reports.store'),
        'method' => 'POST',
        'report' => new App\Models\Report()
    ])
@endsection

