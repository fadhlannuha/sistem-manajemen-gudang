<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Http\Controllers;

use App\Modules\Reporting\Http\Requests\ActivityFilterRequest;
use App\Modules\Reporting\Models\ActivityLog;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(ActivityFilterRequest $request): View
    {
        $this->authorize('viewAny', ActivityLog::class);

        $filters = $request->validated();

        $logs = ActivityLog::query()
            ->when(
                isset($filters['event']) && $filters['event'] !== '',
                fn ($query) => $query->where('event', $filters['event']),
            )
            ->when(
                isset($filters['from']) && $filters['from'] !== '',
                fn ($query) => $query->whereDate('created_at', '>=', $filters['from']),
            )
            ->when(
                isset($filters['to']) && $filters['to'] !== '',
                fn ($query) => $query->whereDate('created_at', '<=', $filters['to']),
            )
            ->when(
                isset($filters['actor']) && $filters['actor'] !== '',
                fn ($query) => $query->where('actor_name', 'like', '%'.$filters['actor'].'%'),
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $events = ActivityLog::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        return view('reporting::activity.index', [
            'logs' => $logs,
            'events' => $events,
            'filters' => $filters,
        ]);
    }
}
