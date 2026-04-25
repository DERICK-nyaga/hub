<?php

namespace App\Controllers;

use Illuminate\Http\Request;
use App\Models\Loss;
use App\Models\Station;
use App\Models\Employee;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class LossController extends Controller
{
        public function index()
    {
        $losses = Loss::with(['station', 'employee'])
            ->orderBy('date_occurred', 'desc')
            ->paginate(20);

        return view('losses.index', compact('losses'));
    }

    public function stationLosses(Station $station)
    {
        $losses = $station->losses()
            ->with('employee')
            ->orderBy('date_occurred', 'desc')
            ->paginate(20);

        return view('losses.station', compact('losses', 'station'));
    }

    public function create()
    {
        $stations = Station::all();
        $employees = Employee::where('status', 'active')->get();

        return view('losses.create', compact('stations', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'station_id' => 'required|exists:stations,id',
            'employee_id' => 'nullable|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:1000',
            'type' => 'required|in:cash,inventory,equipment,other',
            'date_occurred' => 'required|date',
        ]);

        Loss::create($validated);

        return redirect()->route('losses.index')
            ->with('success', 'Loss recorded successfully');
    }

    public function show(Loss $loss)
    {
        return view('losses.show', compact('loss'));
    }

    public function edit(Loss $loss)
    {
        $stations = Station::all();
        $employees = Employee::where('status', 'active')->get();
        return view('loss.edit', compact('loss', 'stations', 'employees'));
    }

    public function update(Request $request, Loss $loss)
    {
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,id',
            'employee_id' => 'nullable|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:1000',
            'type' => 'required|in:cash,inventory,equipment,other',
            'date_occurred' => 'required|date',
        ]);

        $loss->update($validated);

        return redirect()->route('losses.index')
            ->with('success', 'Loss updated successfully');
    }
    public function destroy(Loss $loss)
    {
        $loss->delete();

        return redirect()->route('losses.index')
            ->with('success', 'Loss deleted successfully');
    }

}
