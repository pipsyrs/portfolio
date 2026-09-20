@extends('layouts.landing')

@section('content')
    {{-- Bagian yang dimatikan di Pengaturan tidak dirender sama sekali,
         sehingga komponennya juga tidak ikut melakukan query. --}}
    @foreach ([
        'hero' => 'sections.hero',
        'about' => 'sections.about',
        'tech' => 'sections.tech-stacks',
        'specialis' => 'sections.specialis',
        'careers' => 'sections.careers',
        'certifications' => 'sections.certifications',
        'projects' => 'sections.projects',
        'contact' => 'sections.contact',
    ] as $key => $component)
        @if (settings()->sectionEnabled($key))
            @livewire($component, [], $key)
        @endif
    @endforeach

    <livewire:sections.footer />
@endsection
