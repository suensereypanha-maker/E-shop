<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Color;
use App\DataTables\Backend\ColorDataTable;
use Illuminate\Http\Request;

class ColorController extends Controller
{

    public function index(ColorDataTable $dataTable)
    {
        return $dataTable->render('backend.colors.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
        ]);

        $validated['status'] = $request->has('status');

        Color::create($validated);

        return redirect()->route('backend.colors.index')->with('success', 'Color created successfully.');
    }

    public function update(Request $request, $id)
    {
        $color = Color::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
        ]);

        $validated['status'] = $request->has('status');

        $color->update($validated);

        return redirect()->route('backend.colors.index')->with('success', 'Color updated successfully.');
    }

    public function destroy($id)
    {
        $color = Color::findOrFail($id);
        $color->delete();

        return redirect()->route('backend.colors.index')->with('success', 'Color deleted successfully.');
    }
}
