<?php

namespace App\Controllers;

use App\Models\VendorCategory;
use Illuminate\Http\Request;

class VendorCategoryController extends Controller
{
    public function index()
    {
        $categories = VendorCategory::latest()->paginate(20);
        return view('vendor-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('vendor-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vendor_categories',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        VendorCategory::create($validated);

        return redirect()->route('vendor-categories.index')
            ->with('success', 'Category created successfully!');
    }

    public function show(VendorCategory $vendorCategory)
    {
        $vendorCategory->load(['vendors' => function($query) {
            $query->latest()->limit(5);
        }]);

        return view('vendor-categories.show', compact('vendorCategory'));
    }

    public function edit(VendorCategory $vendorCategory)
    {
        return view('vendor-categories.edit', compact('vendorCategory'));
    }

    public function update(Request $request, VendorCategory $vendorCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vendor_categories,name,'.$vendorCategory->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $vendorCategory->update($validated);

        return redirect()->route('vendor-categories.show', $vendorCategory)
            ->with('success', 'Category updated successfully!');
    }

    public function destroy(VendorCategory $vendorCategory)
    {
        if ($vendorCategory->vendors()->exists()) {
            return back()->with('error', 'Cannot delete: Category has associated vendors!');
        }

        $vendorCategory->delete();

        return redirect()->route('vendor-categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}
