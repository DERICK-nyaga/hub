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
            // Basic Information
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:stations,code',
            'status' => 'nullable|in:active,inactive,pending',
            
            // Location & Address
            'location' => 'required|string|max:255',
            'address' => 'nullable|string',
            'region' => 'nullable|string|max:100',
            
            // Contact Information
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            'mobile_number' => 'nullable|string|max:30',
            
            // Financial Information
            'monthly_loss' => 'required|numeric',
            'deductions' => 'nullable|numeric',
            'opening_date' => 'nullable|date',
            
            // Additional Information
            'notes' => 'nullable|string',
        ]);

        $station = Station::create($validated);

        return redirect()->route('stations.index')->with('success', 'Station created successfully!');
    }

    public function show(Station $station)
    {
        $station->loadCount(['employees', 'payments'])
                ->load(['employees' => function($query) {
                    $query->withCount('deductions')
                          ->withSum('deductions', 'amount');
                }]);

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
            // Basic Information
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:stations,code,' . $station->station_id . ',station_id',
            'status' => 'nullable|in:active,inactive,pending',
            
            // Location & Address
            'location' => 'required|string|max:255',
            'address' => 'nullable|string',
            'region' => 'nullable|string|max:100',
            
            // Contact Information
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            'mobile_number' => 'nullable|string|max:30',
            
            // Financial Information
            'monthly_loss' => 'required|numeric',
            'deductions' => 'nullable|numeric',
            'opening_date' => 'nullable|date',
            
            // Additional Information
            'notes' => 'nullable|string',
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