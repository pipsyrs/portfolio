<section id="hero" class="relative flex items-center pt-32 pb-20 md:pt-40 md:pb-24 overflow-hidden">
    <!-- Soft cursor-follow glow — quiet depth instead of a busy animated background -->
    <div id="hero-spotlight" class="absolute inset-0 z-0" style="background: radial-gradient(600px circle at var(--x, 50%) var(--y, 15%), color-mix(in srgb, var(--primary) 12%, transparent), transparent 70%); opacity: 0; transition: opacity 0.4s ease;" onmouseenter="this.style.opacity=1" onmouseleave="this.style.opacity=0"></div>

    <div class="container mx-auto px-6 md:px-12 lg:px-24 relative z-10">
        <div class="flex flex-col md:flex-row items-center justify-between gap-16">

            <!-- Text Content -->
            <div class="w-full md:w-3/5 text-center md:text-left" data-aos="fade-up">
                <div class="mono inline-flex items-center px-3.5 py-1.5 rounded-full mb-6 text-xs" style="background-color: color-mix(in srgb, var(--primary) 10%, transparent); border: 1px solid color-mix(in srgb, var(--primary) 22%, transparent);">
                    <span class="w-1.5 h-1.5 rounded-full mr-2 animate-pulse" style="background-color: #10b981;"></span>
                    <span class="font-medium uppercase tracking-wide" style="color: var(--primary);">
                        <span class="i18n-en">status: available_for_work</span><span class="i18n-id">status: tersedia_untuk_bekerja</span>
                    </span>
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-[1.12] tracking-tight" style="color: var(--ink);">
                    {{ $user->name ?? 'Jhon Doe' }}
                    <br>
                    <span style="color: var(--primary);">{{ $user->specialis ?? 'Developer' }}</span>
                </h1>

                <p class="text-lg md:text-xl mb-10 max-w-xl leading-relaxed" style="color: var(--ink-soft);">
                    {!! bt_dynamic($user->headline ?? 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod.') !!}
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center md:justify-start gap-4 mb-14">
                    <a href="#projects" class="magnetic btn-primary w-full sm:w-auto px-7 py-3.5 rounded-xl text-center">
                        {!! bt('View My Work') !!}
                        <i class="fas fa-arrow-right ml-2 text-sm"></i>
                    </a>
                    @if(settings('github_link'))
                        <a href="{{ settings('github_link') }}" target="_blank" class="magnetic btn-secondary w-full sm:w-auto px-7 py-3.5 rounded-xl flex items-center justify-center">
                            <i class="fab fa-github mr-2"></i> {!! bt('GitHub Profile') !!}
                        </a>
                    @endif
                </div>

                <!-- Stats -->
                <div class="flex items-center justify-center md:justify-start gap-8 sm:gap-10">
                    @if($user->experience)
                        <div>
                            <p class="text-3xl font-bold" style="color: var(--ink);"><span data-countup="{{ $user->experience }}">0</span>+</p>
                            <p class="text-xs mt-1" style="color: var(--ink-soft);"><span class="i18n-en">Years Experience</span><span class="i18n-id">Tahun Pengalaman</span></p>
                        </div>
                    @endif
                    @if($projectsCount > 0)
                        <div>
                            <p class="text-3xl font-bold" style="color: var(--ink);"><span data-countup="{{ $projectsCount }}">0</span>+</p>
                            <p class="text-xs mt-1" style="color: var(--ink-soft);"><span class="i18n-en">Projects Completed</span><span class="i18n-id">Proyek Selesai</span></p>
                        </div>
                    @endif
                    @if($certificationsCount > 0)
                        <div>
                            <p class="text-3xl font-bold" style="color: var(--ink);"><span data-countup="{{ $certificationsCount }}">0</span>+</p>
                            <p class="text-xs mt-1" style="color: var(--ink-soft);">{!! bt('Certifications') !!}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Portrait -->
            <div class="w-full md:w-2/5 flex justify-center" data-aos="fade-up" data-aos-delay="120">
                <div class="card rounded-3xl p-2 w-64 h-64 md:w-80 md:h-80 shadow-sm">
                    <img src="{{ safe_image_url($user->foto) }}" alt="{{ $user->name ?? 'Profile' }}" class="w-full h-full rounded-[1.35rem] object-cover">
                </div>
            </div>

        </div>
    </div>
</section>
