@props(['active' => false, 'href' => '#'])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 shadow-sm transition-all group'
            : 'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-white/[0.04] border border-transparent transition-all group';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    <span class="{{ ($active ?? false) ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }} transition-colors">
        {{ $icon ?? '' }}
    </span>
    <span class="truncate flex-1">{{ $slot }}</span>
    @if($active ?? false)
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)] shrink-0"></span>
    @endif
</a>
