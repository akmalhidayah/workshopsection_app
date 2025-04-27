<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitWork;

class UnitWorkController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
    
        $units = UnitWork::when($search, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%');
        })->orderBy('name')->paginate(10);
    
        return view('admin.unit_work.index', compact('units', 'search'));
    }
    

    public function create()
    {
        return view('admin.unit_work.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:unit_work,name',
        ]);

        UnitWork::create(['name' => $request->name]);

        return redirect()->route('admin.unit_work.index')->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    public function edit(UnitWork $unitWork)
    {
        return view('admin.unit_work.edit', compact('unitWork'));
    }

    public function update(Request $request, UnitWork $unitWork)
    {
        $request->validate([
            'name' => 'required|string|unique:unit_work,name,' . $unitWork->id,
        ]);

        $unitWork->update(['name' => $request->name]);

        return redirect()->route('admin.unit_work.index')->with('success', 'Unit kerja berhasil diperbarui.');
    }

    public function destroy(UnitWork $unitWork)
    {
        $unitWork->delete();

        return redirect()->route('admin.unit_work.index')->with('success', 'Unit kerja berhasil dihapus.');
    }
}
