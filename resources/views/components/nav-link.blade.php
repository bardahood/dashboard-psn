@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-2.5 py-1.5 rounded-lg text-sm font-semibold text-blue-800 bg-blue-50 transition-colors duration-150 whitespace-nowrap'
            : 'inline-flex items-center px-2.5 py-1.5 rounded-lg text-sm font-medium text-gray-600 hover:text-blue-800 hover:bg-gray-50 transition-colors duration-150 whitespace-nowrap';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
