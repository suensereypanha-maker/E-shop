<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Size;
use App\DataTables\Backend\SizeDataTable;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display a listing of the sizes.
     */
    public function index(SizeDataTable $dataTable)
    {
        return $dataTable->render('backend.sizes.index');
    }

    /**
     * Store a newly created size in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
        ]);

        $validated['status'] = $request->has('status');

        Size::create($validated);

        return redirect()->route('backend.size.index')->with('success', 'Size created successfully.');
    }

    /**
     * Update the specified size in storage.
     */
    public function update(Request $request, $id)
    {
        $size = Size::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
        ]);

        $validated['status'] = $request->has('status');

        $size->update($validated);

        return redirect()->route('backend.size.index')->with('success', 'Size updated successfully.');
    }

    /**
     * Remove the specified size from storage.
     */
    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();

        return redirect()->route('backend.size.index')->with('success', 'Size deleted successfully.');
    }
}
