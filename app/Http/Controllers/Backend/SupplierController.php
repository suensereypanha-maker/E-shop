<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Supplier;
use App\DataTables\Backend\SupplierDataTable;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(SupplierDataTable $dataTable)
    {
        return $dataTable->render('backend.suppliers.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'nullable|string|max:50|unique:suppliers,code',
            'company_name'   => 'nullable|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Supplier::generateCode();
        }

        $validated['status'] = $request->has('status');
        $validated['created_by'] = auth()->id();

        Supplier::create($validated);

        return redirect()->route('backend.suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'nullable|string|max:50|unique:suppliers,code,' . $supplier->id,
            'company_name'   => 'nullable|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = $supplier->code ?: Supplier::generateCode();
        }

        $validated['status'] = $request->has('status');
        $validated['updated_by'] = auth()->id();

        $supplier->update($validated);

        return redirect()->route('backend.suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        $supplier->deleted_by = auth()->id();
        $supplier->save();

        $supplier->delete();

        return redirect()->route('backend.suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
