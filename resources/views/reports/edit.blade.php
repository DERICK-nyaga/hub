@extends('layouts.app')

@section('content')
    <h1>Edit Report</h1>
    @include('reports.form', [
        'action' => route('reports.update', $report->id),
        'method' => 'PUT',
        'report' => $report
    ])
@endsection
