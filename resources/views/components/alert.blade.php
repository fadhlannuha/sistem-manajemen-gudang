@props(['type' => 'success', 'message' => null])

@php
    $classes = $type === 'error'
        ? 'rounded-lg bg-red-100 px-4 py-3 text-sm text-red-800'
        : 'rounded-lg bg-green-100 px-4 py-3 text-sm text-green-800';
@endphp

@if ($message)
    <div {{ $attributes->merge(['class' => $classes]) }} role="status">
        {{ $message }}
    </div>
@endif
