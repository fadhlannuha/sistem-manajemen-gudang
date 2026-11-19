@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Activity Log</h1>
            <p class="mt-1 text-sm text-gray-600">
                Riwayat aktivitas penting di seluruh modul. Halaman ini hanya untuk admin.
            </p>
        </div>

        <form method="GET" action="{{ route('activity.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="event" class="mb-1 block text-sm font-semibold text-gray-700">Event</label>
                    <select name="event" id="event"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        <option value="">Semua event</option>
                        @foreach ($events as $event)
                            <option value="{{ $event }}" @selected((string) ($filters['event'] ?? '') === (string) $event)>
                                {{ $event }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="from" class="mb-1 block text-sm font-semibold text-gray-700">Dari</label>
                    <input type="date" name="from" id="from" value="{{ $filters['from'] ?? '' }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label for="to" class="mb-1 block text-sm font-semibold text-gray-700">Sampai</label>
                    <input type="date" name="to" id="to" value="{{ $filters['to'] ?? '' }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label for="actor" class="mb-1 block text-sm font-semibold text-gray-700">Aktor</label>
                    <input type="text" name="actor" id="actor" value="{{ $filters['actor'] ?? '' }}"
                           placeholder="Nama pengguna"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        Filter
                    </button>
                    <a href="{{ route('activity.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Aktor</th>
                            <th class="px-4 py-3">Event</th>
                            <th class="px-4 py-3">Subjek</th>
                            <th class="px-4 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($logs as $log)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ $log->created_at?->format('d-m-Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $log->actor_name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-gray-700">
                                    {{ $log->event }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    {{ $log->subject_type ?? '—' }}
                                    @if ($log->subject_id)
                                        <span class="text-gray-500">#{{ $log->subject_id }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-900">{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                    Belum ada aktivitas yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection