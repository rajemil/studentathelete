<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'SAIMS') }} — Sports Analytics Platform</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-future antialiased" data-landing>
        <!-- Background system -->
        <div class="pointer-events-none fixed inset-0 z-0 glow-spots"></div>
        <div class="pointer-events-none fixed inset-0 z-0 stars-overlay"></div>
        <div class="pointer-events-none fixed inset-0 z-0 grid-overlay"></div>
        <div class="pointer-events-none fixed inset-0 z-0 grid-fine-overlay"></div>
        <div class="pointer-events-none fixed inset-0 z-0 particles-overlay"></div>
        <div class="pointer-events-none fixed inset-0 z-0 noise opacity-20"></div>

        <div class="relative z-10">
        <header class="sticky top-0 z-30">
            <div class="bg-black/10 backdrop-blur-xl border-b border-white/10">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="flex h-16 items-center justify-between">
                        <a href="/" class="group inline-flex items-center gap-3">
                            <div class="relative grid place-items-center h-10 w-10 rounded-2xl bg-white/5 border border-white/10">
                                <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity" style="box-shadow: 0 0 0 1px rgba(255,122,26,0.25), 0 0 28px rgba(255,122,26,0.16);"></div>
                                <span class="text-sm font-semibold tracking-tight">
                                    <span class="accent-orange">A</span>I
                                </span>
                            </div>
                            <div class="text-sm sm:text-base font-semibold tracking-[0.18em]">
                                <span class="accent-orange">A</span>THLETEAI
                            </div>
                        </a>

                        <nav class="hidden md:flex items-center gap-1 text-sm">
                            <a href="#features" class="rounded-xl px-4 py-2 text-white/70 hover:text-white hover:bg-white/5 transition-colors">Features</a>
                            <a href="#how" class="rounded-xl px-4 py-2 text-white/70 hover:text-white hover:bg-white/5 transition-colors">How</a>
                        </nav>

                        <div class="flex items-center gap-2">
                            <a data-motion-btn href="{{ route('login') }}" class="hidden sm:inline-flex rounded-xl px-4 py-2 text-sm font-semibold text-white/70 hover:text-white hover:bg-white/5 transition-colors">
                                Login
                            </a>
                            <a
                                data-motion-btn
                                href="{{ route('register') }}"
                                class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold text-black transition-all
                                       bg-gradient-to-r from-[#FF7A1A] via-[#FF8A3A] to-[#FFB24D]
                                       shadow-[0_10px_30px_rgba(255,122,26,0.18)]
                                       hover:shadow-[0_14px_46px_rgba(255,122,26,0.32)]
                                       hover:-translate-y-0.5 active:translate-y-0"
                            >
                                Get Started
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero (reference-style) -->
        <section class="relative z-10 overflow-hidden" data-reveal-group="hero">
            <div class="mx-auto max-w-7xl px-6 pt-14 pb-16 lg:pt-20 lg:pb-20">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    <!-- LEFT -->
                    <div class="lg:col-span-6" data-reveal>
                        <div class="inline-flex items-center gap-2 rounded-full glass px-4 py-2 text-[11px] tracking-[0.22em] text-white/70">
                            PERFORMANCE ANALYTICS
                        </div>

                        <h1 class="mt-7 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.02] text-white">
                            TRACK. IMPROVE.<br>
                            <span class="accent-orange">PERFORM BETTER</span>
                        </h1>

                        <p class="mt-5 text-base sm:text-lg text-white/70 max-w-xl">
                            Real-time scoring, predictive insights, and training plans that help athletes stay consistent, reduce risk, and show up ready on game day.
                        </p>

                        <ul class="mt-7 space-y-3 text-sm text-white/75">
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500/15 ring-1 ring-emerald-400/25">
                                    <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5 text-emerald-300">
                                        <path d="M16.25 5.75L8.25 13.75L3.75 9.25" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span>Track performance trends and rankings across every sport.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500/15 ring-1 ring-emerald-400/25">
                                    <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5 text-emerald-300">
                                        <path d="M16.25 5.75L8.25 13.75L3.75 9.25" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span>Predict outcomes with confidence-aware scoring signals.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500/15 ring-1 ring-emerald-400/25">
                                    <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5 text-emerald-300">
                                        <path d="M16.25 5.75L8.25 13.75L3.75 9.25" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span>Get weekly training plans that adapt to fatigue + performance drops.</span>
                            </li>
                        </ul>

                        <div class="mt-9 flex flex-col sm:flex-row gap-3">
                            <a
                                data-motion-btn
                                href="{{ route('register') }}"
                                class="inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold text-black transition-all
                                       bg-gradient-to-r from-[#FF7A1A] via-[#FF8A3A] to-[#FFB24D]
                                       shadow-[0_12px_38px_rgba(255,122,26,0.20)]
                                       hover:shadow-[0_18px_56px_rgba(255,122,26,0.34)]
                                       hover:-translate-y-0.5 active:translate-y-0"
                            >
                                Get Started
                            </a>
                            <a
                                data-motion-btn
                                href="#preview"
                                class="inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold text-white/85
                                       bg-white/0 border border-white/12 hover:border-white/18 hover:bg-white/5 transition-colors"
                            >
                                View Dashboard
                            </a>
                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="lg:col-span-6 relative" data-hero-parallax>
                        <div class="relative aspect-[5/6] sm:aspect-[4/3] lg:aspect-[16/13] rounded-3xl overflow-hidden border border-white/10 bg-white/5 backdrop-blur-xl [perspective:1000px] flex flex-col">
                            <!-- orange glow framing -->
                            <div class="pointer-events-none absolute -inset-10 opacity-70" style="background: radial-gradient(420px 520px at 66% 42%, rgba(255,122,26,0.22), transparent 65%);"></div>
                            <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(900px 500px at 70% 30%, rgba(255,122,26,0.10), transparent 60%);"></div>

                            <!-- soft background orbs (depth layers) -->
                            <div class="pointer-events-none absolute -left-10 top-10 h-56 w-56 rounded-full blur-3xl opacity-30 z-0"
                                 style="background: radial-gradient(circle at 30% 30%, rgba(255,122,26,0.55), transparent 60%);"
                                 data-parallax data-depth="0.18"></div>
                            <div class="pointer-events-none absolute left-28 -bottom-12 h-64 w-64 rounded-full blur-3xl opacity-20 z-0"
                                 style="background: radial-gradient(circle at 40% 40%, rgba(255,178,77,0.45), transparent 62%);"
                                 data-parallax data-depth="0.12"></div>
                            <div class="pointer-events-none absolute -right-12 top-24 h-60 w-60 rounded-full blur-3xl opacity-20 z-0"
                                 style="background: radial-gradient(circle at 35% 35%, rgba(255,122,26,0.40), transparent 60%);"
                                 data-parallax data-depth="0.10"></div>

                            <!-- 3D glowing human figure -->
                            <div class="relative flex-1 min-h-0 z-10" data-hero-3d data-parallax data-depth="0.30"></div>

                            <!-- Floating dashboard card -->
                            <div class="relative z-20 mx-auto w-[calc(100%-2.5rem)] max-w-[360px] -mt-16 mb-5 sm:mb-0 sm:mt-0 sm:absolute sm:right-6 sm:bottom-6 sm:w-[320px] sm:mx-0 rounded-2xl glass p-5 glow-border-orange hover:scale-[1.03] transition-transform"
                                 data-anti-grav data-parallax data-depth="0.55">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-[11px] tracking-[0.18em] text-white/60">SCORE</div>
                                        <div class="mt-2 flex items-end gap-2">
                                            <div class="text-3xl font-semibold">85</div>
                                            <div class="pb-1 text-sm text-white/60">/ 100</div>
                                        </div>
                                    </div>
                                    <div class="rounded-xl bg-white/5 px-3 py-2 text-xs text-white/70 ring-1 ring-white/10">
                                        This week
                                    </div>
                                </div>

                                <div class="mt-4 rounded-xl bg-black/30 ring-1 ring-white/10 p-3">
                                    <div class="flex items-center justify-between text-[11px] text-white/60">
                                        <span>Trend</span>
                                        <span class="text-emerald-300">+6.2%</span>
                                    </div>
                                    <svg viewBox="0 0 240 44" class="mt-2 h-10 w-full">
                                        <defs>
                                            <linearGradient id="lineOrange" x1="0" x2="1">
                                                <stop offset="0" stop-color="#FF7A1A" stop-opacity="0.15"/>
                                                <stop offset="1" stop-color="#FFB24D" stop-opacity="0.35"/>
                                            </linearGradient>
                                            <linearGradient id="strokeOrange" x1="0" x2="1">
                                                <stop offset="0" stop-color="#FF7A1A"/>
                                                <stop offset="1" stop-color="#FFB24D"/>
                                            </linearGradient>
                                        </defs>
                                        <path d="M2 34 C 26 30, 40 12, 62 18 S 106 38, 132 24 S 172 10, 198 18 S 220 34, 238 14" fill="none" stroke="url(#strokeOrange)" stroke-width="2.4" stroke-linecap="round"/>
                                        <path d="M2 44 L2 34 C 26 30, 40 12, 62 18 S 106 38, 132 24 S 172 10, 198 18 S 220 34, 238 14 L238 44 Z" fill="url(#lineOrange)"/>
                                    </svg>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                                    <div class="rounded-xl bg-white/5 p-3 ring-1 ring-white/10">
                                        <div class="text-white/60">Stamina</div>
                                        <div class="mt-1 text-sm font-semibold">Good</div>
                                    </div>
                                    <div class="rounded-xl bg-white/5 p-3 ring-1 ring-white/10">
                                        <div class="text-white/60">Risk</div>
                                        <div class="mt-1 text-sm font-semibold">Low</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-xs text-white/45">
                            Hover the card for a subtle scale.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Engineered for Excellence -->
        <section class="relative z-10" data-reveal-group="engineered">
            <div class="mx-auto max-w-7xl px-6 py-24">
                <div class="max-w-2xl" data-reveal>
                    <div class="text-sm text-white/60">I am a</div>
                    <h2 class="mt-2 text-3xl sm:text-4xl font-semibold tracking-[0.12em]">ENGINEERED FOR EXCELLENCE</h2>
                </div>

                <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @php
                        $engineered = [
                            [
                                'title' => 'Speed Progress',
                                'desc' => 'See momentum instantly with clean trends, rankings, and score deltas—built for fast decisions.',
                                'icon' => 'bolt',
                            ],
                            [
                                'title' => 'Weekly Training Goals',
                                'desc' => 'Turn performance signals into weekly targets with routines that stay realistic and measurable.',
                                'icon' => 'target',
                            ],
                            [
                                'title' => 'Injury Prevention',
                                'desc' => 'Spot fatigue early and adjust load with risk-aware recommendations that protect consistency.',
                                'icon' => 'shield',
                            ],
                            [
                                'title' => 'Team Synergy',
                                'desc' => 'Build lineups that complement strengths with matchup-ready, data-driven team suggestions.',
                                'icon' => 'users',
                            ],
                        ];
                    @endphp

                    @foreach($engineered as $c)
                        <x-ui.card class="p-6" data-reveal>
                            <div class="flex items-center justify-between">
                                <div class="h-11 w-11 rounded-2xl bg-white/5 ring-1 ring-white/10 grid place-items-center
                                            group-hover:shadow-[0_0_0_1px_rgba(255,122,26,0.25),0_0_32px_rgba(255,122,26,0.18)] transition-shadow">
                                    @if($c['icon'] === 'bolt')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-white/80">
                                            <path d="M13 2L3 14h8l-1 8 11-14h-8l0-6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        </svg>
                                    @elseif($c['icon'] === 'target')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-white/80">
                                            <path d="M12 22a10 10 0 1 0-7.07-2.93A9.96 9.96 0 0 0 12 22Z" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M12 18a6 6 0 1 0-4.24-1.76A5.98 5.98 0 0 0 12 18Z" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M12 14a2 2 0 1 0-1.41-.59A1.99 1.99 0 0 0 12 14Z" stroke="currentColor" stroke-width="1.8"/>
                                        </svg>
                                    @elseif($c['icon'] === 'shield')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-white/80">
                                            <path d="M12 2 20 6v7c0 5-3.5 9-8 9s-8-4-8-9V6l8-4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="M9 12l2 2 4-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-white/80">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M9 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1 text-white/35 group-hover:text-white/45 transition-colors">
                                    <div class="h-2 w-2 rounded-full bg-emerald-400/40 ring-1 ring-emerald-400/20"></div>
                                    <div class="h-2 w-2 rounded-full bg-sky-400/40 ring-1 ring-sky-400/20"></div>
                                    <div class="h-2 w-2 rounded-full bg-white/10 ring-1 ring-white/10"></div>
                                </div>
                            </div>

                            <div class="mt-5 text-lg font-semibold tracking-tight">
                                {{ $c['title'] }}
                            </div>
                            <div class="mt-2 text-sm text-white/70">
                                {{ $c['desc'] }}
                            </div>

                            <div class="mt-6 flex items-center gap-2 text-white/40">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white/5 ring-1 ring-white/10 group-hover:ring-white/15 transition-colors">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                        <path d="M4 19V5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M4 15c2-1 3-4 5-4s3 3 5 3 3-4 5-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M20 19V5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white/5 ring-1 ring-white/10 group-hover:ring-white/15 transition-colors">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                        <path d="M12 2v20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M2 12h20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white/5 ring-1 ring-white/10 group-hover:ring-white/15 transition-colors">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                        <path d="M8 12h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M12 8v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M21 12a9 9 0 1 1-9-9 9 9 0 0 1 9 9Z" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                </span>
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="relative z-10" data-reveal-group="features">
            <div class="mx-auto max-w-7xl px-6 py-24">
                <div class="max-w-2xl" data-reveal>
                    <div class="text-sm text-indigo-200/90">Built for performance programs</div>
                    <h2 class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight">Everything you need to manage athletes and outcomes</h2>
                    <p class="mt-4 text-white/70">From rankings and draft lists to predictive analytics and training recommendations—delivered in an enterprise-grade UI.</p>
                </div>

                <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @php
                        $features = [
                            ['title' => 'Performance trends', 'desc' => 'Track scoring patterns over time with confidence-aware forecasts.'],
                            ['title' => 'Rankings & draft list', 'desc' => 'Compute sport-specific rankings and draft-ready lists instantly.'],
                            ['title' => 'Win probability', 'desc' => 'Compare lineups using weighted strength models and probabilities.'],
                            ['title' => 'Role-based dashboards', 'desc' => 'Admin, Coach, Student dashboards with tailored insights.'],
                            ['title' => 'Team builder', 'desc' => 'Generate strongest or balanced teams using snake draft logic.'],
                            ['title' => 'Recommendations', 'desc' => 'Training routines, game strategy hints, and prep dates driven by data.'],
                        ];
                    @endphp
                    @foreach($features as $f)
                        <x-ui.card class="p-6" hoverGlow="true" data-reveal>
                            <div class="h-10 w-10 rounded-2xl bg-white/10 flex items-center justify-center ring-1 ring-white/10">
                                <div class="h-4 w-4 rounded bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D]"></div>
                            </div>
                            <div class="mt-4 text-lg font-semibold">{{ $f['title'] }}</div>
                            <div class="mt-2 text-sm text-white/70">{{ $f['desc'] }}</div>
                        </x-ui.card>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Analytics Preview -->
        <section id="how" class="relative z-10" data-reveal-group="how">
            <div class="mx-auto max-w-7xl px-6 py-24">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div class="lg:col-span-5">
                        <div class="inline-flex items-center gap-2 rounded-full glass px-4 py-2 text-xs text-white/75">
                            <span class="h-2 w-2 rounded-full accent-orange-bg"></span>
                            How it works
                        </div>
                        <h2 class="mt-5 text-2xl sm:text-3xl font-semibold tracking-tight">
                            Capture → Score → Predict → Improve.
                        </h2>
                        <p class="mt-3 text-white/70">
                            ATHLETEAI turns PE scoring and athlete stats into forecasts, risk signals, and weekly training plans—so coaches and students always know what to do next.
                        </p>
                    </div>
                    <div class="lg:col-span-7">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="glass rounded-2xl p-5">
                                <div class="text-sm font-semibold">1) Record scores</div>
                                <div class="mt-2 text-sm text-white/70">Enter PE performance scoring per sport. Students automatically see updates.</div>
                            </div>
                            <div class="glass rounded-2xl p-5">
                                <div class="text-sm font-semibold">2) Generate insights</div>
                                <div class="mt-2 text-sm text-white/70">Trends, rankings, and top-performer signals refresh instantly.</div>
                            </div>
                            <div class="glass rounded-2xl p-5">
                                <div class="text-sm font-semibold">3) Predict outcomes</div>
                                <div class="mt-2 text-sm text-white/70">Win probability, strongest lineup, and performance forecasts—on demand.</div>
                            </div>
                            <div class="glass rounded-2xl p-5">
                                <div class="text-sm font-semibold">4) Train smarter</div>
                                <div class="mt-2 text-sm text-white/70">Weekly plans adapt to fatigue + performance drops to reduce injury risk.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="preview" class="relative z-10" data-reveal-group="preview">
            <div class="mx-auto max-w-7xl px-6 py-24">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <div class="lg:col-span-5" data-reveal>
                        <div class="text-sm text-emerald-200/90">Analytics preview</div>
                        <h2 class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight">Dashboards that feel like enterprise software</h2>
                        <p class="mt-4 text-white/70">Glassmorphism cards, clean typography, and charts that communicate what matters—fast.</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <div class="rounded-full glass px-4 py-2 text-xs text-white/75">Chart.js-ready</div>
                            <div class="rounded-full glass px-4 py-2 text-xs text-white/75">Role-gated</div>
                            <div class="rounded-full glass px-4 py-2 text-xs text-white/75">Mobile-first</div>
                        </div>
                    </div>

                    <div class="lg:col-span-7" data-reveal>
                        <x-ui.card class="p-5 overflow-hidden" hoverGlow="true">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="rounded-2xl bg-white/5 p-4 border border-white/10 backdrop-blur-lg shadow-lg">
                                    <div class="text-xs text-white/60">Students</div>
                                    <div class="mt-2 text-3xl font-semibold">1,248</div>
                                    <div class="mt-1 text-xs text-white/60">Across 12 sports</div>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-4 border border-white/10 backdrop-blur-lg shadow-lg">
                                    <div class="text-xs text-white/60">Upcoming events</div>
                                    <div class="mt-2 text-3xl font-semibold">18</div>
                                    <div class="mt-1 text-xs text-white/60">Next 30 days</div>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-4 border border-white/10 backdrop-blur-lg shadow-lg">
                                    <div class="text-xs text-white/60">Avg score</div>
                                    <div class="mt-2 text-3xl font-semibold">82.7</div>
                                    <div class="mt-1 text-xs text-emerald-300">Trending up</div>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="rounded-2xl bg-white/5 p-4 border border-white/10 backdrop-blur-lg shadow-lg">
                                    <div class="text-xs text-white/60">Performance trend</div>
                                    <div class="mt-3 h-28 rounded-xl bg-gradient-to-r from-indigo-400/20 via-sky-400/10 to-emerald-400/20 border border-white/10"></div>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-4 border border-white/10 backdrop-blur-lg shadow-lg">
                                    <div class="text-xs text-white/60">Top athletes</div>
                                    <div class="mt-3 space-y-2">
                                        @foreach([['A. Santos', 92],['J. Kim', 90],['M. Reyes', 88]] as $row)
                                            <div class="flex items-center justify-between rounded-xl bg-white/5 px-3 py-2">
                                                <div class="text-sm">{{ $row[0] }}</div>
                                                <div class="text-sm font-semibold">{{ $row[1] }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </x-ui.card>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section id="testimonials" class="relative z-10" data-reveal-group="testimonials">
            <div class="mx-auto max-w-7xl px-6 py-24">
                <div class="flex items-end justify-between gap-6" data-reveal>
                    <div class="max-w-2xl">
                        <div class="text-sm text-indigo-200/90">Trusted by programs</div>
                        <h2 class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight">Teams move faster with clear insight</h2>
                        <p class="mt-4 text-white/70">A modern system that replaces spreadsheets and scattered notes with a single source of truth.</p>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach([
                        ['quote' => 'We cut planning time in half and improved lineup decisions immediately.', 'name' => 'Head Coach', 'org' => 'University Athletics'],
                        ['quote' => 'The rankings and win probability changed how we prepare for meets.', 'name' => 'PE Instructor', 'org' => 'Public School District'],
                        ['quote' => 'Clean UI, fast workflows, and the analytics are easy to trust.', 'name' => 'Athletic Director', 'org' => 'Private Academy'],
                    ] as $t)
                        <x-ui.card class="p-6" data-reveal>
                            <div class="text-sm text-white/80">“{{ $t['quote'] }}”</div>
                            <div class="mt-5 flex items-center gap-3">
                                <div class="h-10 w-10 rounded-2xl bg-white/10"></div>
                                <div>
                                    <div class="text-sm font-semibold">{{ $t['name'] }}</div>
                                    <div class="text-xs text-white/60">{{ $t['org'] }}</div>
                                </div>
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Mobile App Showcase -->
        <section id="mobile" class="relative z-10" data-reveal-group="mobile">
            <div class="mx-auto max-w-7xl px-6 py-24">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-6" data-reveal>
                        <div class="text-sm text-emerald-200/90">Mobile app showcase</div>
                        <h2 class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight">Insights on the move</h2>
                        <p class="mt-4 text-white/70">Coaches can check performance, events, and recommendations anywhere—optimized for small screens.</p>
                        <div class="mt-6 flex gap-3">
                            <div class="rounded-full glass px-4 py-2 text-xs text-white/75">iOS-ready UI</div>
                            <div class="rounded-full glass px-4 py-2 text-xs text-white/75">Android-ready UI</div>
                        </div>
                    </div>

                    <div class="lg:col-span-6" data-reveal>
                        <x-ui.card class="relative p-6 overflow-hidden" hoverGlow="true">
                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-indigo-500/15 to-emerald-400/10"></div>
                            <div class="relative grid grid-cols-2 gap-4">
                                <x-ui.card class="p-4 aspect-[9/16]" hoverGlow="false" data-float data-float-amp="10">
                                    <div class="text-xs text-white/60">Student</div>
                                    <div class="mt-2 text-lg font-semibold">Trend ↑</div>
                                    <div class="mt-4 h-20 rounded-2xl bg-white/5 border border-white/10"></div>
                                    <div class="mt-4 space-y-2">
                                        <div class="h-10 rounded-2xl bg-white/5 border border-white/10"></div>
                                        <div class="h-10 rounded-2xl bg-white/5 border border-white/10"></div>
                                    </div>
                                </x-ui.card>
                                <x-ui.card class="p-4 aspect-[9/16]" hoverGlow="false" data-float data-float-amp="12" data-float-dur="5.8">
                                    <div class="text-xs text-white/60">Coach</div>
                                    <div class="mt-2 text-lg font-semibold">Lineup</div>
                                    <div class="mt-4 space-y-2">
                                        <div class="h-10 rounded-2xl bg-white/5 border border-white/10"></div>
                                        <div class="h-10 rounded-2xl bg-white/5 border border-white/10"></div>
                                        <div class="h-10 rounded-2xl bg-white/5 border border-white/10"></div>
                                    </div>
                                </x-ui.card>
                            </div>
                        </x-ui.card>
                    </div>
                </div>
            </div>
        </section>

        <!-- Blog -->
        <section id="blog" class="relative z-10" data-reveal-group="blog">
            <div class="mx-auto max-w-7xl px-6 py-24">
                <div class="max-w-2xl" data-reveal>
                    <div class="text-sm text-indigo-200/90">Blog</div>
                    <h2 class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight">Insights for better programs</h2>
                    <p class="mt-4 text-white/70">Practical articles on athlete development, analytics, and preparation.</p>
                </div>

                <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach([
                        ['title' => 'How to build fair rankings across sports', 'tag' => 'Analytics', 'date' => 'Mar 2026'],
                        ['title' => 'Turning scores into training plans', 'tag' => 'Coaching', 'date' => 'Mar 2026'],
                        ['title' => 'Win probability: what it means and what it doesn’t', 'tag' => 'Strategy', 'date' => 'Mar 2026'],
                    ] as $b)
                        <a href="#" class="block" data-reveal>
                            <x-ui.card class="p-6">
                                <div class="text-xs text-white/60">{{ $b['tag'] }} · {{ $b['date'] }}</div>
                                <div class="mt-3 text-lg font-semibold text-white">{{ $b['title'] }}</div>
                                <div class="mt-3 text-sm text-white/70">Read more →</div>
                            </x-ui.card>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-white/10">
            <div class="mx-auto max-w-7xl px-6 py-24">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-10">
                    <div class="md:col-span-5">
                        <div class="flex items-center gap-2">
                            <div class="h-9 w-9 rounded-xl glass flex items-center justify-center">S</div>
                            <div class="text-sm font-semibold">SAIMS</div>
                        </div>
                        <p class="mt-4 text-sm text-white/60 max-w-sm">
                            Student Athlete Information Management System with enterprise-grade analytics and modern UI.
                        </p>
                        <div class="mt-6 text-xs text-white/40">© {{ date('Y') }} SAIMS. All rights reserved.</div>
                    </div>

                    <div class="md:col-span-7 grid grid-cols-2 sm:grid-cols-3 gap-6 text-sm">
                        <div>
                            <div class="text-white/80 font-semibold">Product</div>
                            <div class="mt-3 space-y-2 text-white/60">
                                <a href="#features" class="block hover:text-white">Features</a>
                                <a href="#preview" class="block hover:text-white">Analytics</a>
                                <a href="#mobile" class="block hover:text-white">Mobile</a>
                            </div>
                        </div>
                        <div>
                            <div class="text-white/80 font-semibold">Company</div>
                            <div class="mt-3 space-y-2 text-white/60">
                                <a href="#testimonials" class="block hover:text-white">Testimonials</a>
                                <a href="#blog" class="block hover:text-white">Blog</a>
                                <a href="#" class="block hover:text-white">Contact</a>
                            </div>
                        </div>
                        <div>
                            <div class="text-white/80 font-semibold">Account</div>
                            <div class="mt-3 space-y-2 text-white/60">
                                <a href="{{ route('login') }}" class="block hover:text-white">Log in</a>
                                <a href="{{ route('register') }}" class="block hover:text-white">Register</a>
                                @auth
                                    <a href="/dashboard" class="block hover:text-white">Dashboard</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        </div>
    </body>
</html>

