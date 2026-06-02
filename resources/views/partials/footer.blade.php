{{-- Footer — design mirrored from the RGU marketing site --}}
<footer id="contact" class="border-t border-white/5 bg-ink2 print:hidden">

    {{-- Top Color Bar --}}
    <div class="flex h-[3px]">
        <div class="flex-1 bg-purple-500"></div>
        <div class="flex-1 bg-sky-400"></div>
        <div class="flex-1 bg-lime-400"></div>
        <div class="flex-1 bg-pink-400"></div>
        <div class="flex-1 bg-orange-400"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-14">
        <div class="grid gap-12 pb-12 grid-cols-1 sm:grid-cols-2 lg:grid-cols-[280px_1fr_1fr_260px]">

            {{-- Brand --}}
            <div>
                <div class="mb-5 inline-flex items-center rounded-xl bg-white/95 px-4 py-2 shadow-lg">
                    <img src="{{ asset('images/logo.png') }}" alt="RGU" class="h-9 w-auto object-contain" />
                </div>
                <p class="mb-6 text-sm leading-7 text-white/40">
                    Rathinam Global University — a leading deemed university in Coimbatore shaping future-ready graduates.
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach (['NAAC A++', 'NBA', 'UGC', 'AICTE', 'QS'] as $tag)
                        <span class="rounded-md border border-lime-400/20 bg-lime-400/10 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-lime-300">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>

            {{-- Quick Links --}}
            <div>
                <h5 class="mb-5 text-xs font-extrabold uppercase tracking-[0.2em] text-white/35">Quick Links</h5>
                <ul class="space-y-3">
                    <li><a href="https://rathinam.global/" class="text-sm text-white/45 transition hover:text-white">About RGU</a></li>
                    <li><a href="https://rathinam.global/" class="text-sm text-white/45 transition hover:text-white">Research</a></li>
                    <li><a href="https://rathinam.global/" class="text-sm text-white/45 transition hover:text-white">Placements</a></li>
                    <li><a href="https://rathinam.global/" class="text-sm text-white/45 transition hover:text-white">Contact</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h5 class="mb-5 text-xs font-extrabold uppercase tracking-[0.2em] text-white/35">Contact</h5>
                <div class="space-y-5">
                    <div class="flex items-start gap-3">
                        <span class="text-white/40">📍</span>
                        <p class="text-sm leading-6 text-white/45">Eachanari, Coimbatore <br /> Tamil Nadu – 641021</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="text-white/40">📞</span>
                        <p class="text-sm leading-6 text-white/45">+91-844-844-8909</p>
                    </div>
                </div>
            </div>

            {{-- Admissions --}}
            <div>
                <h5 class="mb-5 text-xs font-extrabold uppercase tracking-[0.2em] text-white/35">Admissions</h5>
                <p class="mb-5 text-sm leading-7 text-white/45">Admissions are now open for Undergraduate, Postgraduate and Research programmes.</p>
                <a href="https://rathinam.global/" class="inline-flex items-center rounded-xl bg-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/20">Explore Programmes</a>
            </div>
        </div>

        {{-- Bottom --}}
        <div class="flex flex-col gap-4 border-t border-white/5 py-6 text-center sm:flex-row sm:items-center sm:justify-between sm:text-left">
            <p class="text-xs leading-6 text-white/25">© {{ date('Y') }} Rathinam Global University. All rights reserved.</p>
        </div>
    </div>
</footer>
