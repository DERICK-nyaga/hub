@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Approval Report</h3>
                </div>
                <div class="card-body">
                    <h4>Summary</h4>
                    <table class="table">
                        <tr>
                            <th>Total Changes:</th>
                            <td>{{ $report['summary']['total_changes'] }}</td>
                        </tr>
                        <tr>
                            <th>Approved Changes:</th>
                            <td>{{ $report['summary']['approved_changes'] }}</td>
                        </tr>
                        <tr>
                            <th>Rejected Changes:</th>
                            <td>{{ $report['summary']['rejected_changes'] }}</td>
                        </tr>
                        <tr>
                            <th>Approval Rate:</th>
                            <td>{{ $report['summary']['approval_rate'] }}%</td>
                        </tr>
                        <tr>
                            <th>Average Approval Time:</th>
                            <td>{{ $report['summary']['average_approval_time'] }} hours</td>
                        </tr>
                    </table>

                    <h4>Terminations Summary</h4>
                    <table class="table">
                        <tr>
                            <th>Total Terminations:</th>
                            <td>{{ $report['terminations']['total'] }}</td>
                        </tr>
                        <tr>
                            <th>Approved Terminations:</th>
                            <td>{{ $report['terminations']['approved'] }}</td>
                        </tr>
                        <tr>
                            <th>Rejected Terminations:</th>
                            <td>{{ $report['terminations']['rejected'] }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
