@php
    $channels = collect([
        [
            'icon' => 'fas fa-envelope',
            'label_en' => 'Email', 'label_id' => 'Email',
            'value' => $email,
            'href' => filled($email) ? 'mailto:'.$email : null,
        ],
        [
            'icon' => 'fa-brands fa-whatsapp',
            'label_en' => 'Phone', 'label_id' => 'Telepon',
            'value' => $phone,
            'href' => filled($phone) ? 'tel:'.preg_replace('/[^0-9+]/', '', $phone) : null,
        ],
        [
            'icon' => 'fa-solid fa-location-dot',
            'label_en' => 'Based in', 'label_id' => 'Berlokasi di',
            'value' => $address,
            'href' => null,
        ],
    ])->filter(fn (array $channel) => filled($channel['value']));
@endphp

<section id="contact" class="lp-section lp-section--open">
    <div class="lp-shell">
        <div class="grid lg:grid-cols-12 gap-x-14 gap-y-12">

            <div class="lg:col-span-5">
                <div class="lg:sticky" style="top: calc(var(--nav-h) + 2rem);">
                    <p class="lp-slug" data-reveal>
                        <span class="idx">08</span>
                        <span class="i18n-en">Contact</span><span class="i18n-id">Kontak</span>
                    </p>

                    <h2 class="lp-h2 mt-6" data-reveal style="--reveal-delay:60ms">
                        <span class="i18n-en">Tell me about the work</span><span class="i18n-id">Ceritakan pekerjaannya</span>
                    </h2>

                    <p class="lp-lead mt-5" data-reveal style="--reveal-delay:100ms">
                        <span class="i18n-en">Send the details and I will reply from the inbox below.</span><span class="i18n-id">Kirim detailnya dan saya balas lewat kotak masuk di bawah.</span>
                    </p>

                    @if ($channels->isNotEmpty())
                        <dl class="contact-list" data-reveal style="--reveal-delay:140ms">
                            @foreach ($channels as $channel)
                                <div class="contact-cell">
                                    <span class="contact-icon" aria-hidden="true">
                                        <i class="{{ $channel['icon'] }}"></i>
                                    </span>
                                    <div class="contact-text">
                                        <dt class="lp-meta">
                                            <span class="i18n-en">{{ $channel['label_en'] }}</span><span class="i18n-id">{{ $channel['label_id'] }}</span>
                                        </dt>
                                        <dd class="contact-value">
                                            @if ($channel['href'])
                                                <a href="{{ $channel['href'] }}">{{ $channel['value'] }}</a>
                                            @else
                                                {{ $channel['value'] }}
                                            @endif
                                        </dd>
                                    </div>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-7">
                @if (session()->has('contact-success'))
                    <div class="contact-note is-ok" role="status" x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms>
                        <i class="fas fa-circle-check mt-0.5" aria-hidden="true"></i>
                        <p>{{ session('contact-success') }}</p>
                        <button type="button" @click="show = false" class="contact-note-x">
                            <i class="fas fa-xmark" aria-hidden="true"></i>
                            <span class="sr-only">{{ bt_variant('Close', app()->getLocale()) }}</span>
                        </button>
                    </div>
                @endif

                @if (session()->has('contact-error'))
                    <div class="contact-note is-bad" role="alert" x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms>
                        <i class="fas fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                        <p>{{ session('contact-error') }}</p>
                        <button type="button" @click="show = false" class="contact-note-x">
                            <i class="fas fa-xmark" aria-hidden="true"></i>
                            <span class="sr-only">{{ bt_variant('Close', app()->getLocale()) }}</span>
                        </button>
                    </div>
                @endif

                <form wire:submit="sendMessage" class="contact-form lp-card" data-reveal>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="lp-field">
                            <label for="contact-name">{!! bt('Name') !!}</label>
                            <input wire:model="name" type="text" id="contact-name" autocomplete="name"
                                   class="i18n-placeholder @error('name') has-error @enderror"
                                   data-ph-en="{{ bt_variant('Your name', 'en') }}" data-ph-id="{{ bt_variant('Your name', 'id') }}"
                                   placeholder="{{ __('Your name') }}">
                            @error('name') <p class="lp-field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="lp-field">
                            <label for="contact-email">{!! bt('Email Address') !!}</label>
                            <input wire:model="senderEmail" type="email" id="contact-email" autocomplete="email"
                                   class="i18n-placeholder @error('senderEmail') has-error @enderror"
                                   data-ph-en="you@example.com" data-ph-id="anda@contoh.com"
                                   placeholder="you@example.com">
                            @error('senderEmail') <p class="lp-field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="lp-field">
                        <label for="contact-subject">{!! bt('Subject') !!}</label>
                        <input wire:model="subject" type="text" id="contact-subject"
                               class="i18n-placeholder @error('subject') has-error @enderror"
                               data-ph-en="{{ bt_variant('What the work is about', 'en') }}" data-ph-id="{{ bt_variant('What the work is about', 'id') }}"
                               placeholder="{{ __('What the work is about') }}">
                        @error('subject') <p class="lp-field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="lp-field">
                        <label for="contact-message">{!! bt('Message') !!}</label>
                        <textarea wire:model="message" id="contact-message" rows="6"
                                  class="i18n-placeholder @error('message') has-error @enderror"
                                  data-ph-en="{{ bt_variant('Scope, timeline, budget, whatever you already know', 'en') }}"
                                  data-ph-id="{{ bt_variant('Scope, timeline, budget, whatever you already know', 'id') }}"
                                  placeholder="{{ __('Scope, timeline, budget, whatever you already know') }}"></textarea>
                        @error('message') <p class="lp-field-error">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="lp-btn lp-btn-primary w-full sm:w-auto sm:self-start" wire:loading.attr="disabled" wire:target="sendMessage">
                        <svg wire:loading wire:target="sendMessage" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="sendMessage">{!! bt('Send Message') !!}</span>
                        <span wire:loading wire:target="sendMessage">{!! bt('Sending...') !!}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
    .contact-list {
        display: grid;
        gap: 0.6rem;
        margin-block-start: 2.5rem;
    }
    .contact-cell {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.9rem 1rem;
        border: 1px solid var(--hairline);
        border-radius: var(--r-ctl);
        background-color: var(--surface-alt);
        box-shadow: var(--inner-lift);
        transition: border-color 0.24s var(--ease-out);
    }
    .contact-cell:hover { border-color: color-mix(in srgb, var(--primary) 45%, var(--hairline)); }
    .contact-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        inline-size: 40px;
        block-size: 40px;
        flex: none;
        border-radius: 11px;
        border: 1px solid var(--hairline);
        background-color: var(--surface);
        color: var(--accent-text);
        font-size: 0.9375rem;
    }
    .contact-text { min-inline-size: 0; }
    .contact-value {
        margin-block-start: 0.15rem;
        font-size: 0.9375rem;
        font-weight: 500;
        color: var(--ink);
        overflow-wrap: anywhere;
    }
    .contact-value a { color: var(--ink); }
    .contact-value a:hover { color: var(--accent-text); text-decoration: underline; text-underline-offset: 3px; }

    .contact-note {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        margin-block-end: 1.5rem;
        padding: 1rem 1.1rem;
        border-radius: var(--r-card);
        font-size: 0.875rem;
        line-height: 1.5;
    }
    .contact-note p { flex: 1; min-inline-size: 0; }
    .contact-note.is-ok {
        background-color: color-mix(in srgb, var(--success) 12%, transparent);
        border: 1px solid color-mix(in srgb, var(--success) 32%, transparent);
        color: color-mix(in srgb, var(--success) 45%, var(--ink));
    }
    .contact-note.is-bad {
        background-color: color-mix(in srgb, var(--danger) 12%, transparent);
        border: 1px solid color-mix(in srgb, var(--danger) 32%, transparent);
        color: color-mix(in srgb, var(--danger) 55%, var(--ink));
    }
    .contact-note-x {
        inline-size: 32px;
        block-size: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--r-tag);
        flex: none;
    }

    .contact-form {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        padding: clamp(1.25rem, 4vw, 2.25rem);
        border-radius: var(--r-slab);
        box-shadow: var(--shadow-md), inset 0 1px 0 var(--glass-sheen);
    }

    .lp-field { display: flex; flex-direction: column; gap: 0.45rem; min-inline-size: 0; }
    .lp-field label { font-size: 0.8125rem; font-weight: 600; color: var(--ink); }
    .lp-field input,
    .lp-field textarea {
        inline-size: 100%;
        min-block-size: 50px;
        padding: 0.85rem 1rem;
        border-radius: var(--r-ctl);
        border: 1px solid var(--hairline);
        background-color: var(--surface);
        color: var(--ink);
        font-size: 0.9375rem;
        transition: border-color 0.2s var(--ease-out), box-shadow 0.2s var(--ease-out);
    }
    .lp-field textarea { resize: vertical; line-height: 1.6; }
    .lp-field input:hover,
    .lp-field textarea:hover { border-color: var(--ink-soft); }
    .lp-field input:focus,
    .lp-field textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 16%, transparent);
    }
    .lp-field input.has-error,
    .lp-field textarea.has-error { border-color: var(--danger); }
    .lp-field-error { font-size: 0.75rem; color: var(--danger); }
</style>
