<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::paginate(10);

        return Inertia::render('Permission', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'menu' => 'nullable|string|max:255',
            'feature' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'alias' => 'nullable|string|max:255',
        ]);

        Permission::create($request->all());

        return redirect()->route('permission.index')->with('success', 'Permission created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'menu' => 'nullable|string|max:255',
            'feature' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'alias' => 'nullable|string|max:255',
        ]);

        $permission->update($request->all());

        return redirect()->route('permission.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('permission.index')->with('success', 'Permission deleted successfully.');
    }
}
