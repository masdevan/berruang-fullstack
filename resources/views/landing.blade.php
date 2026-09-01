<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F7F0EB">
    <meta name="description" content="BerRuang is a quieter place for private conversations, shared spaces, and the people who matter.">
    <title>{{ config('app.name') }} - Make room for your people</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
    @vite('resources/css/app.css')
</head>
<body class="font-sans antialiased bg-[#F7F0EB] text-[#321B27] touch-manipulation">
    <div class="min-h-dvh overflow-x-clip">
        <nav class="sticky top-0 z-50 border-b border-[#321B27]/15 bg-[#F7F0EB]">
            <div class="max-w-6xl mx-auto px-5 sm:px-8 py-4 flex items-center justify-between gap-6">
                <a href="/landing" class="flex items-center gap-2.5 shrink-0">
                    <img src="{{ asset('landing/logo.png') }}" alt="BerRuang" class="w-8 h-8 object-contain">
                    <span class="text-sm font-semibold tracking-[-0.02em]">BerRuang</span>
                </a>
                <div class="flex items-center gap-1 sm:gap-3">
                    <a href="#spaces" class="hidden sm:block px-3 py-2 text-xs font-medium text-[#321B27]/55 hover:text-[#321B27] transition-colors">Spaces</a>
                    <a href="#features" class="hidden sm:block px-3 py-2 text-xs font-medium text-[#321B27]/55 hover:text-[#321B27] transition-colors">Features</a>
                    <a href="{{ route('login') }}" class="px-3 py-2 text-xs font-medium text-[#321B27]/65 hover:text-[#321B27] transition-colors">Sign in</a>
                    <a href="{{ route('register') }}" class="px-4 py-2.5 bg-[#321B27] text-[#F7F0EB] text-xs font-semibold rounded-sm hover:bg-[#4A2635] transition-colors">Get started</a>
                </div>
            </div>
        </nav>

        <div class="relative min-h-[calc(100vh-65px)] flex items-center border-b border-[#321B27]/15 overflow-hidden">
            <div
                class="absolute top-0 left-0 w-full h-[320px] lg:h-full lg:w-1/2 opacity-[0.08] pointer-events-none"
                style="background-image:url('{{ asset('landing/seamless_pattern.png') }}');background-size:340px;"
            ></div>

            <div class="relative z-10 max-w-6xl mx-auto w-full px-5 sm:px-8 py-10 md:py-12">
                <div class="grid grid-cols-1 lg:grid-cols-[1.18fr_0.82fr] gap-6 lg:gap-2 items-center">

                    <div class="relative min-h-[220px] sm:min-h-[350px] lg:min-h-[480px] flex items-center justify-center lg:justify-start order-1 lg:order-1">
                        <img
                            src="{{ asset('landing/image_4.png') }}"
                            alt="BerRuang, a space for connection"
                            class="relative z-10 w-full max-w-[300px] sm:max-w-[420px] lg:max-w-[560px] object-contain"
                        >
                    </div>

                    <div class="relative z-10 order-2 lg:order-2 text-center lg:text-left">
                        <h1 class="max-w-3xl mx-auto lg:mx-0 text-[clamp(2.6rem,5.5vw,4.8rem)] font-semibold tracking-[-0.05em] leading-[0.95]">
                            Make room<br>
                            for <span class="font-serif italic font-normal text-[#D83F70]">your people.</span>
                        </h1>

                        <p class="max-w-md mx-auto lg:mx-0 mt-6 text-base leading-relaxed text-[#321B27]/65">
                            Private chats, shared spaces, and the small moments in between. BerRuang keeps the conversation close and the noise out.
                        </p>

                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 mt-7">
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center gap-3 px-5 py-3 bg-[#ED3F78] text-[#321B27] text-sm font-semibold rounded-sm hover:bg-[#F25A8D] transition-colors"
                            >
                                Create your space
                                <span aria-hidden="true">&rarr;</span>
                            </a>

                            <a
                                href="#spaces"
                                class="px-3 py-3 text-sm font-medium text-[#321B27]/60 hover:text-[#321B27] transition-colors"
                            >
                                See how it works
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <section class="relative h-screen w-full bg-[#321B27] text-[#F7F0EB] overflow-hidden flex items-center" aria-label="BerRuang introduction">
            <div class="relative w-full max-w-6xl mx-auto px-5 sm:px-8 py-14 md:py-20">
                <div class="max-w-5xl">
                    <h1 class="max-w-5xl font-serif text-4xl sm:text-6xl lg:text-7xl leading-[1.05] tracking-[-0.04em] text-balance">
                        Good conversations <em class="italic text-[#F7F0EB]/80 font-light">need a little room.</em>
                        <span class="block mt-2 text-[#F7F0EB]/50 font-light">Not another feed. Not another notification machine.</span>
                    </h1>
                </div>
            </div>
        </section>

        <section id="spaces" class="scroll-mt-10 border-b border-[#321B27]/15">
            <div class="max-w-6xl mx-auto px-5 sm:px-8 py-16 md:py-24 grid lg:grid-cols-[1.15fr_0.85fr] gap-10 lg:gap-20 items-center">
                <div class="relative min-h-[280px] sm:min-h-[420px] flex items-end justify-center overflow-hidden">
                    <img src="{{ asset('landing/image_1.png') }}" alt="Four BerRuang characters gathered around a table" loading="lazy" class="relative z-10 w-[110%] max-w-[680px] object-contain object-bottom">
                </div>
                <div>
                    <h2 class="mt-0 text-3xl md:text-5xl font-semibold tracking-[-0.06em] leading-[0.95]">Bring the whole table with you.</h2>
                    <p class="mt-6 text-sm md:text-base leading-relaxed text-[#321B27]/60">Workspaces give your group a place that feels like its own. Invite with a code, give people the right role, and let the conversation grow naturally.</p>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-3 mt-7 text-sm font-semibold text-[#D83F70] hover:text-[#321B27] transition-colors">Start a workspace <span aria-hidden="true">&rarr;</span></a>
                </div>
            </div>
        </section>

        <section id="features" class="scroll-mt-10 bg-[#ED3F78] text-[#321B27] overflow-hidden">
            <div class="max-w-6xl mx-auto px-5 sm:px-8 py-16 md:py-24 grid grid-cols-1 lg:grid-cols-[0.8fr_1.2fr] gap-8 lg:gap-12 items-center">

                <div class="relative min-h-[320px] sm:min-h-[460px] lg:min-h-[540px] flex items-center justify-center lg:justify-end order-1 lg:order-2">
                    <div class="absolute left-1/2 lg:left-auto lg:right-0 top-1/2 -translate-x-1/2 lg:translate-x-0 -translate-y-1/2 w-[90%] sm:w-[72%] aspect-square">
                        <div class="absolute inset-0 rounded-full border border-[#321B27]/20"></div>
                        <div class="absolute inset-[12.5%] rounded-full border border-[#321B27]/15"></div>
                    </div>

                    <img
                        src="{{ asset('landing/image_2.png') }}"
                        alt="BerRuang character chatting on a phone"
                        loading="lazy"
                        class="relative z-10 w-full max-w-[520px] max-h-[560px] object-contain"
                    >
                </div>

                <div class="relative z-10 text-center lg:text-left order-2 lg:order-1">
                    <h2 class="mt-0 text-3xl md:text-5xl font-semibold tracking-[-0.06em] leading-[0.95]">
                        Still close,<br>even on the move.
                    </h2>

                    <p class="max-w-sm mx-auto lg:mx-0 mt-6 text-sm md:text-base leading-relaxed text-[#321B27]/65">
                        A simple chat that respects your attention. Send a thought, share a photo, and know when it reaches the other side.
                    </p>
                </div>

            </div>
        </section>

        <section class="relative h-screen flex items-center justify-center border-b border-[#321B27]/15 overflow-hidden">
            <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-[#ED3F78]/5 blur-3xl pointer-events-none"></div>
            <div class="absolute -left-32 bottom-0 w-[28rem] h-[28rem] rounded-full bg-[#321B27]/5 blur-3xl pointer-events-none"></div>
            <div class="relative w-full max-w-6xl mx-auto px-5 sm:px-8 py-12 md:py-16 text-center">
                <div class="max-w-3xl mx-auto">
                    <h2 class="text-4xl md:text-6xl lg:text-7xl font-semibold tracking-[-0.06em] leading-[0.95] text-balance">
                        Small signals.<br>
                        <span class="font-serif italic font-normal text-[#ED3F78]">Less guessing.</span>
                    </h2>
                    <p class="mt-8 max-w-md mx-auto text-base md:text-lg leading-relaxed text-[#321B27]/65">
                        Everything you need to stay close without the clutter. Know who's around, keep shared things findable, and make every space feel like yours.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-[#321B27] text-[#F7F0EB] overflow-hidden">
            <div class="max-w-6xl mx-auto px-5 sm:px-8 py-16 md:py-24 grid grid-cols-1 lg:grid-cols-[0.85fr_1.15fr] gap-10 lg:gap-16 items-center">

                <div class="order-2 lg:order-1 text-center lg:text-left">
                    <h2 class="mt-0 text-3xl md:text-5xl font-semibold tracking-[-0.06em] leading-[0.95]">
                        There is always room for one more.
                    </h2>

                    <p class="max-w-md mx-auto lg:mx-0 mt-6 text-sm md:text-base leading-relaxed text-[#F7F0EB]/55">
                        From a small circle to a full team, BerRuang keeps the feeling of a shared place. Come as you are. Bring the people with you.
                    </p>

                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center gap-3 mt-8 px-5 py-3 bg-[#ED3F78] text-[#321B27] text-sm font-semibold rounded-sm hover:bg-[#F25A8D] transition-colors"
                    >
                        Join the room
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>

                <div class="order-1 lg:order-2 relative min-h-[300px] sm:min-h-[500px] flex items-end justify-center">
                    <img
                        src="{{ asset('landing/image_3.png') }}"
                        alt="Eight BerRuang characters hugging together"
                        loading="lazy"
                        class="w-full max-w-[620px] object-contain object-bottom"
                    >
                </div>

            </div>
        </section>

        <footer class="bg-[#F7F0EB]">
            <div class="max-w-6xl mx-auto px-5 sm:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#321B27]/50">
                <a href="/landing" class="flex items-center gap-2 font-semibold text-[#321B27]">
                    <img src="{{ asset('landing/logo.png') }}" alt="" class="w-6 h-6 object-contain">
                    BerRuang
                </a>
                <p>&copy; {{ date('Y') }} BerRuang. A little more room to talk.</p>
            </div>
        </footer>
    </div>
</body>
</html>
