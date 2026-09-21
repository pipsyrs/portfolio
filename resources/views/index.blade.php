@extends('layouts.landing')

@section('content')
    {{-- Bagian yang dimatikan di Pengaturan tidak dirender sama sekali,
         sehingga komponennya juga tidak ikut melakukan query. --}}
    @foreach ([
        'hero' => 'sections.hero',
        'about' => 'sections.about',
        'tech' => 'sections.tech-stacks',
        'security' => null,
        'specialis' => 'sections.specialis',
        'careers' => 'sections.careers',
        'certifications' => 'sections.certifications',
        'projects' => 'sections.projects',
        'contact' => 'sections.contact',
    ] as $key => $component)
        @if (settings()->sectionEnabled($key))
            @if ($component === null)
                {{-- Keamanan tidak menyentuh basis data, jadi ia hanya komponen
                     Blade statis — tanpa komponen Livewire yang kosong isinya. --}}
                <x-landing.security />
            @else
                @livewire($component, [], $key)
            @endif
        @endif
    @endforeach

    <livewire:sections.footer />
@endsection
