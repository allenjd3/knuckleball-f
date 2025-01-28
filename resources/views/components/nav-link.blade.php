@props(['active'])

@php
$classes = ($active ?? false)
            ? 'uppercase inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-custom-600 hover:text-custom-600 focus:outline-none focus:border-red-700 transition duration-150 ease-in-out'
            : 'uppercase inline-flex items-center px-1 pt-1 border-transparent text-sm font-medium hover:text-custom-600 leading-5 text-gray-500 focus:outline-none focus:text-custom-600 focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

<a style="--c-600: var(--primary-600);" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
