<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'sort' => 'nullable|string|in:created_at',
            'direction' => 'nullable|string|in:asc,desc',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|in:10,20,30,40,50',
        ]);

        $logs = Activity::with('causer')
            ->when($request->input('search'), function ($query, $search) {
                $query->whereHas('causer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->when($request->input('start_date'), function ($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($request->input('end_date'), function ($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->orderBy($request->input('sort', 'created_at'), $request->input('direction', 'desc'))
            ->paginate($request->input('per_page', 10))
            ->withQueryString();

        return Inertia::render('ActivityLog', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'sort', 'direction', 'start_date', 'end_date', 'per_page']),
        ]);
    }
}
