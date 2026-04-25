<?php

namespace App\Controllers;

use App\Models\Vendor;
use App\Models\VendorCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with('category')->latest()->paginate(20);
        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        $categories = VendorCategory::all();
        return view('vendors.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vendors',
            'category_id' => 'required|exists:vendor_categories,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'account_number' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'payment_terms' => 'required|in:net_15,net_30,net_60,due_on_receipt',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string'
        ]);

        if ($request->hasFile('contract_file')) {
            $validated['contract_path'] = $request->file('contract_file')->store('vendor-contracts');
        }

        Vendor::create($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully!');
    }

    public function show(Vendor $vendor)
    {
        return view('vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        $categories = VendorCategory::all();
        return view('vendors.edit', compact('vendor', 'categories'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vendors,name,'.$vendor->id,
            'category_id' => 'required|exists:vendor_categories,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'account_number' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'payment_terms' => 'required|in:net_15,net_30,net_60,due_on_receipt',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'is_active' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        if ($request->hasFile('contract_file')) {
            if ($vendor->contract_path) {
                Storage::delete($vendor->contract_path);
            }
            $validated['contract_path'] = $request->file('contract_file')->store('vendor-contracts');
        }

        $vendor->update($validated);

        return redirect()->route('vendors.show', $vendor)->with('success', 'Vendor updated successfully!');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->contract_path) {
            Storage::delete($vendor->contract_path);
        }

        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully!');
    }

    public function downloadContract(Vendor $vendor)
    {
        if (!$vendor->contract_path) {
            abort(404);
        }

        return Storage::download($vendor->contract_path, "{$vendor->name}-contract.pdf");
    }
}
