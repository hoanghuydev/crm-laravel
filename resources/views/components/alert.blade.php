@props(['type' => 'info', 'message'])

@php
    $classes = [
        'info' => 'bg-blue-100 border-blue-400 text-blue-700',
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
    ];
@endphp

<div class="border px-4 py-3 rounded relative mb-4 {{ $classes[$type] }}" role="alert">
    <span class="block sm:inline">{{ $message ?? $slot }}</span>
</div>
