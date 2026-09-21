<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Category;
use App\DataTables\Backend\CategoryDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    public function index(CategoryDataTable $dataTable)
    {
        $categories = Category::select('id', 'name', 'parent_id')
            ->orderBy('name')
            ->get();

        return $dataTable->render('backend.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'nullable|string|max:120|unique:categories,slug',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $validated['slug'] = !empty($validated['slug']) 
            ? Str::slug($validated['slug']) 
            : Str::slug($validated['name']);

        // Check if auto-generated slug already exists
        $slugCount = Category::where('slug', $validated['slug'])->count();
        if ($slugCount > 0) {
            $validated['slug'] .= '-' . time();
        }

        $validated['parent_id'] = $request->filled('parent_id') ? $request->parent_id : null;
        $validated['status'] = $request->has('status');
        $validated['created_by'] = auth()->id();

        // Handle Image Upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'cat_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/categories');
            
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }
            
            $image->move($destinationPath, $imageName);
            $validated['image'] = 'uploads/categories/' . $imageName;
        }

        Category::create($validated);

        return redirect()->route('backend.categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'nullable|string|max:120|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id|not_in:' . $category->id,
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $validated['slug'] = !empty($validated['slug']) 
            ? Str::slug($validated['slug']) 
            : Str::slug($validated['name']);

        $slugCount = Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->count();
        if ($slugCount > 0) {
            $validated['slug'] .= '-' . time();
        }

        $validated['parent_id'] = ($request->filled('parent_id') && $request->parent_id != $category->id)
            ? $request->parent_id 
            : null;

        $validated['status'] = $request->has('status');
        $validated['updated_by'] = auth()->id();

        // Handle Image Replacement
        if ($request->hasFile('image')) {
            if ($category->image && File::exists(public_path($category->image))) {
                File::delete(public_path($category->image));
            }

            $image = $request->file('image');
            $imageName = 'cat_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/categories');

            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }

            $image->move($destinationPath, $imageName);
            $validated['image'] = 'uploads/categories/' . $imageName;
        }

        $category->update($validated);

        return redirect()->route('backend.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Track who soft-deleted the record
        $category->deleted_by = auth()->id();
        $category->save();
        
        $category->delete();

        return redirect()->route('backend.categories.index')->with('success', 'Category deleted successfully.');
    }
}
