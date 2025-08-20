@props(['type' => 'primary', 'size' => 'md', 'href' => null, 'submit' => false])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition duration-150 ease-in-out';
    
    $sizeClasses = [
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
    
    $typeClasses = [
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500',
        'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
        'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white focus:ring-yellow-500',
        'outline' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 focus:ring-blue-500',
    ];
    
    $classes = $baseClasses . ' ' . $sizeClasses[$size] . ' ' . $typeClasses[$type];
    
    // Determine button type - if submit is true, use submit, otherwise button
    $buttonType = $submit ? 'submit' : 'button';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => $buttonType]) }}>
        {{ $slot }}
    </button>
@endif
