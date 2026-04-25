<?php

namespace App\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StationController extends Controller
{
    public function index(Station $station)
    {
        $stations = Station::withCount('employees', 'payments')->paginate(10);
        return view('stations.index', compact('stations'));
    }

    public function create()
    {
        return view('stations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:30|regex:/^\+?[0-9\-\s\(\)]{7,20}$/',
            'monthly_loss' => 'required|numeric',
            'deductions' => 'nullable|numeric',
        ]);

        Station::create($validated);

        return redirect()->route('stations.index')->with('success', 'Station created successfully!');
    }

public function show(Station $station)
{
    $station->loadCount(['employees', 'payments'])
            ->load(['employees' => function($query) {
                $query->withCount('deductions')
                      ->withSum('deductions', 'amount');
            }]);

                if ($station->employees->count() > 0) {
        foreach ($station->employees as $employee) {
            Log::info('Employee deductions data:', [
                'employee_id' => $employee->employee_id,
                'name' => $employee->full_name,
                'deductions_count' => $employee->deductions_count,
                'deductions_sum_amount' => $employee->deductions_sum_amount,
                'has_deductions_relation' => method_exists($employee, 'deductions')
            ]);
        }
    }

    $station->loadSum('payments', 'amount');

    return view('stations.show', compact('station'));
}
    public function edit(Station $station)
    {
        return view('stations.edit', compact('station'));
    }

    public function update(Request $request, Station $station)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:30|regex:/^\+?[0-9\-\s\(\)]{7,20}$/',
            'monthly_loss' => 'required|numeric',
            'deductions' => 'nullable|numeric',
        ]);

        $station->update($validated);

        return redirect()->route('stations.index')->with('success', 'Station updated successfully!');
    }

    public function destroy(Station $station)
    {
        $station->delete();
        return redirect()->route('stations.index')->with('success', 'Station deleted successfully!');
    }
}
