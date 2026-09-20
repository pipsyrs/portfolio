<footer class="pt-16 pb-8" style="border-top: 1px solid var(--hairline);">
    <div class="container mx-auto px-6 md:px-12 lg:px-24">
        <div class="flex flex-col md:flex-row justify-between items-center md:items-start mb-12">
            <!-- Brand -->
            <div class="mb-8 md:mb-0 text-center md:text-left">
                <a href="#" class="text-lg font-bold tracking-tight inline-block mb-4" style="color: var(--ink);">
                    PORT<span style="color: var(--primary);">FOLIO</span>
                </a>
                <p class="text-sm max-w-xs mx-auto md:mx-0 leading-relaxed" style="color: var(--ink-soft);">
                    {!! bt('Building digital experiences that combine beautiful design with elegant code.') !!}
                </p>
            </div>

            <!-- Social Links -->
            @if (settings('youtube_link') || settings('linkedin_link') || settings('instagram_link') || settings('github_link') || settings('tiktok_link') || settings('facebook_link') || settings('x_twitter_link'))
                <div class="text-center md:text-right">
                    <h4 class="font-semibold mb-4 text-sm uppercase tracking-wider" style="color: var(--ink);">{!! bt('Connect') !!}</h4>
                    <div class="flex space-x-3 justify-center md:justify-end">
                        @php
                            $socials = [
                                ['link' => settings('youtube_link'), 'icon' => 'fab fa-youtube', 'label' => 'YouTube'],
                                ['link' => settings('linkedin_link'), 'icon' => 'fab fa-linkedin-in', 'label' => 'LinkedIn'],
                                ['link' => settings('instagram_link'), 'icon' => 'fab fa-instagram', 'label' => 'Instagram'],
                                ['link' => settings('github_link'), 'icon' => 'fab fa-github', 'label' => 'GitHub'],
                                ['link' => settings('tiktok_link'), 'icon' => 'fab fa-tiktok', 'label' => 'TikTok'],
                                ['link' => settings('facebook_link'), 'icon' => 'fab fa-facebook-f', 'label' => 'Facebook'],
                                ['link' => settings('x_twitter_link'), 'icon' => 'fab fa-twitter', 'label' => 'X'],
                            ];
                        @endphp
                        @foreach($socials as $social)
                            @if($social['link'])
                                <a href="{{ $social['link'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}" class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-200 hover:-translate-y-0.5" style="background-color: var(--surface-alt); border: 1px solid var(--hairline); color: var(--ink-soft);" onmouseenter="this.style.backgroundColor='var(--primary)'; this.style.color='#fff'; this.style.borderColor='var(--primary)';" onmouseleave="this.style.backgroundColor='var(--surface-alt)'; this.style.color='var(--ink-soft)'; this.style.borderColor='var(--hairline)';">
                                    <i class="{{ $social['icon'] }}"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Security note -->
        <div class="mono inline-flex items-center gap-2 px-3.5 py-2 rounded-lg mb-8 text-xs" style="background-color: color-mix(in srgb, var(--primary) 8%, transparent); border: 1px solid color-mix(in srgb, var(--primary) 18%, transparent); color: var(--ink-soft);">
            <i class="fas fa-lock" style="color: var(--primary);"></i>
            <span class="i18n-en">Assets & files are served through signed, time-limited URLs — nothing here is public by accident.</span>
            <span class="i18n-id">Aset & file disajikan lewat URL bertanda tangan yang punya masa berlaku — tidak ada yang publik secara tidak sengaja.</span>
        </div>

        <!-- Copyright -->
        <div class="pt-8 flex flex-col md:flex-row justify-between items-center text-sm gap-2" style="border-top: 1px solid var(--hairline); color: var(--ink-soft);">
            <p>&copy; {{ date('Y') }} {{ $user->name }}. {!! bt('All Rights Reserved.') !!}</p>
            <span>{!! bt('Thanks for checking out my portfolio!') !!}</span>
        </div>
    </div>
</footer>
