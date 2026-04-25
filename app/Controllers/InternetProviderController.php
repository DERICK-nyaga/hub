<?php

namespace App\Controllers;

use App\Models\InternetProvider;
use Illuminate\Http\Request;

class InternetProviderController extends Controller
{
    public function index()
    {
        $providers = InternetProvider::orderBy('name')->paginate(20);
        return view('internet-providers.index', compact('providers'));
    }

    public function create()
    {
        return view('internet-providers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:fiber,wireless,satellite,cable',
            'paybill_number' => 'nullable|string|max:50',
            'account_prefix' => 'required|string|max:20|unique:internet_providers,account_prefix',
            'support_contact' => 'required|string|max:50',
            'billing_email' => 'nullable|email|max:255',
            'standard_amount' => 'nullable|numeric|min:0',
            'due_day' => 'nullable|integer|min:1|max:31',
            'grace_period_days' => 'nullable|integer|min:0|max:30',
        ]);

        InternetProvider::create($validated);

        return redirect()->route('internet-providers.index')
            ->with('success', 'Internet provider created successfully!');
    }

    public function show(InternetProvider $internetProvider)
    {
        return view('internet-providers.show', compact('internetProvider'));
    }

    public function edit(InternetProvider $internetProvider)
    {
        return view('internet-providers.edit', compact('internetProvider'));
    }

    public function update(Request $request, InternetProvider $internetProvider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:fiber,wireless,satellite,cable',
            'paybill_number' => 'nullable|string|max:50',
            'account_prefix' => 'required|string|max:20|unique:internet_providers,account_prefix,' . $internetProvider->vendor_id . ',vendor_id',
            'support_contact' => 'required|string|max:50',
            'billing_email' => 'nullable|email|max:255',
            'standard_amount' => 'nullable|numeric|min:0',
            'due_day' => 'nullable|integer|min:1|max:31',
            'grace_period_days' => 'nullable|integer|min:0|max:30',
        ]);

        $internetProvider->update($validated);

        return redirect()->route('internet-providers.index')
            ->with('success', 'Internet provider updated successfully!');
    }

    public function destroy(InternetProvider $internetProvider)
    {
        $internetProvider->delete();

        return redirect()->route('internet-providers.index')
            ->with('success', 'Internet provider deleted successfully!');
    }
}
