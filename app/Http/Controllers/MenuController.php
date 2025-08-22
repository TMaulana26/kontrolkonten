<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortField = $request->input('sort', 'order');
        $sortDirection = $request->input('direction', 'asc');
        $allowedSorts = ['id', 'name', 'route', 'order', 'status', 'created_at'];

        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'order';
        }

        $menus = Menu::query()
            ->when($request->input('search'), function ($query, $search) {
                // Search across translatable fields
                $query->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->id', 'like', "%{$search}%")
                    ->orWhere('route', 'like', "%{$search}%");
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($request->input('per_page', 10))
            ->withQueryString();

        $trashedMenus = Menu::onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        return Inertia::render('Menu', [
            'menus' => $menus,
            'trashedMenus' => $trashedMenus,
            'filters' => $request->only(['search', 'sort', 'direction', 'per_page']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.id' => 'required|string|max:255',
            'description' => 'required|array',
            'description.en' => 'required|string|max:255',
            'description.id' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'order' => 'required|integer',
        ]);

        Menu::create($request->all());

        $menu = Menu::latest()->first();

        Log::info("Menu with id:({$menu->id}), name:({$menu->name}) created");

        return redirect()->route('menu.index')->with('success', 'Menu created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.id' => 'required|string|max:255',
            'description' => 'required|array',
            'description.en' => 'required|string|max:255',
            'description.id' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'order' => 'required|integer',
        ]);

        $menu->update($request->all());

        Log::info("Menu with id:({$menu->id}), name:({$menu->name}) updated");

        return redirect()->route('menu.index')->with('success', 'Menu updated successfully.');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(Menu $menu)
    {
        $menu->status = !$menu->status;
        $menu->save();

        Log::info("Menu with id:({$menu->id}), name:({$menu->name}) status updated");

        return redirect()->back()->with('success', 'Menu status updated.');
    }

    /**
     * Remove the specified resource from storage.
     * This will now perform a SOFT delete.
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();

        Log::info("Menu with id:({$menu->id}), name:({$menu->name}) moved to trash");

        return redirect()->route('menu.index')->with('success', 'Menu moved to trash.');
    }

    /**
     * Restore the specified soft-deleted resource.
     */
    public function restore($id)
    {
        $menu = Menu::onlyTrashed()->findOrFail($id);
        $menu->restore();

        Log::info("Menu with id:({$id}), name:({$menu->name}) restored");

        return redirect()->route('menu.index')->with('success', 'Menu restored successfully.');
    }

    /**
     * Permanently delete the specified resource from storage.
     */
    public function forceDelete($id)
    {
        $menu = Menu::onlyTrashed()->findOrFail($id);
        $menu->forceDelete();

        Log::info("Menu with id:({$id}), name:({$menu->name}) permanently deleted");

        return redirect()->route('menu.index')->with('success', 'Menu permanently deleted.');
    }
}
