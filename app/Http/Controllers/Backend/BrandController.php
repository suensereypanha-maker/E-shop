<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Brands;
use App\DataTables\Backend\BrandDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class BrandController extends Controller
{
    public function index(BrandDataTable $dataTable)
    {
        return $dataTable->render('backend.brands.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'nullable|string|max:120|unique:brands,slug',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $validated['slug'] = !empty($validated['slug']) 
            ? Str::slug($validated['slug']) 
            : Str::slug($validated['name']);

        // Check if auto-generated slug already exists
        $slugCount = Brands::where('slug', $validated['slug'])->count();
        if ($slugCount > 0) {
            $validated['slug'] .= '-' . time();
        }

        $validated['status'] = $request->has('status');
        $validated['created_by'] = auth()->id();

        // Handle Image Upload
        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $imageName = 'brand_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/brands');
            
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }
            
            $image->move($destinationPath, $imageName);
            $validated['logo'] = 'uploads/brands/' . $imageName;
        }

        Brands::create($validated);

        return redirect()->route('backend.brands.index')->with('success', 'Brand created successfully.');
    }

    public function update(Request $request, $id)
    {
        $brand = Brands::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'nullable|string|max:120|unique:brands,slug,' . $brand->id,
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $validated['slug'] = !empty($validated['slug']) 
            ? Str::slug($validated['slug']) 
            : Str::slug($validated['name']);

        $validated['status'] = $request->has('status');
        $validated['updated_by'] = auth()->id();

        // Handle Logo Replacement
        if ($request->hasFile('logo')) {
            if ($brand->logo && File::exists(public_path($brand->logo))) {
                File::delete(public_path($brand->logo));
            }

            $image = $request->file('logo');
            $imageName = 'brand_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/brands');

            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }

            $image->move($destinationPath, $imageName);
            $validated['logo'] = 'uploads/brands/' . $imageName;
        }

        $brand->update($validated);

        return redirect()->route('backend.brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy($id)
    {
        $brand = Brands::findOrFail($id);
        
        // Track who soft-deleted the record
        $brand->deleted_by = auth()->id();
        $brand->save();
        
        $brand->delete();

        return redirect()->route('backend.brands.index')->with('success', 'Brand deleted successfully.');
    }
}
