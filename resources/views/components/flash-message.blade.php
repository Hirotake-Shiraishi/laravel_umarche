@props(['status' => 'info'])

@php
    if (session('status') === 'alert') {
        $classes = 'bg-red-50 text-red-800 border-red-200';
    } else {
        $classes = 'bg-blue-50 text-blue-900 border-blue-200';
    }
@endphp

@if (session('message'))
    <div role="status"
        class="{{ $classes }} w-full min-w-0 max-w-none mb-4 px-3 py-3 sm:px-4 sm:py-3 rounded-lg border text-sm sm:text-base leading-relaxed break-words shadow-sm">
        {{ session('message') }}
    </div>
@endif
