@props([
    'hoverGlow' => true,
])

<div {{ $attributes->class([
        'group relative overflow-hidden rounded-2xl bg-white/5 backdrop-blur-lg border border-white/10 shadow-lg transition-all duration-300',
        'hover:-translate-y-1 hover:shadow-[0_0_0_1px_rgba(255,122,26,0.22),0_30px_110px_rgba(255,122,26,0.16)]' => $hoverGlow,
    ]) }}
>
    <!-- inner gradient overlay -->
    <div class="pointer-events-none absolute inset-0 opacity-70" style="background: linear-gradient(135deg, rgba(255,255,255,0.10), transparent 45%, rgba(255,122,26,0.06));"></div>

    <!-- subtle reflections -->
    <div class="pointer-events-none absolute -top-24 -left-24 h-64 w-64 rounded-full opacity-35 blur-3xl"
         style="background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.18), transparent 60%);"></div>
    <div class="pointer-events-none absolute -bottom-28 -right-28 h-72 w-72 rounded-full opacity-25 blur-3xl"
         style="background: radial-gradient(circle at 40% 40%, rgba(255,122,26,0.16), transparent 62%);"></div>

    <div class="relative">
        {{ $slot }}
    </div>
</div>
