<div class="space-y-6">

    <x-dash.page-header title="Projects"
                        eyebrow="Konten"
                        subtitle="{{ $projects->total() }} project tampil di landing page.">
        <x-slot:actions>
    <div class="flex items-center gap-2">
                <div class="w-52">
                    <x-dash.search wire:model.live.debounce.400ms="search" placeholder="Cari project…" />
        </x-slot:actions>
    </x-dash.page-header>

            <x-dash.button href="{{ route('dashboard.projects.create') }}" wire:navigate icon="fa-solid fa-plus">
                Tambah
            </x-dash.button>
        </div>
    </div>

    @if ($projects->isEmpty())
        <x-dash.card padding="p-0">
            <x-dash.empty-state icon="fa-solid fa-folder-open"
                                title="{{ $search ? 'Tidak ada project yang cocok' : 'Belum ada project' }}"
                                description="{{ $search ? 'Coba kata kunci lain.' : 'Tambahkan project pertama untuk ditampilkan di landing page.' }}">
                <x-slot:action>
                    <x-dash.button href="{{ route('dashboard.projects.create') }}" wire:navigate icon="fa-solid fa-plus" size="sm">
                        Tambah Project
                    </x-dash.button>
                </x-slot:action>
            </x-dash.empty-state>
        </x-dash.card>
    @else
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($projects as $project)
                <article wire:key="project-{{ $project->id }}" class="card card-hover flex flex-col overflow-hidden rounded-2xl">

                    <div class="relative aspect-[16/10] overflow-hidden" style="background-color: var(--surface-alt);">
                        <img src="{{ safe_image_url($project->image, 'projects') }}" alt=""
                             loading="lazy" class="h-full w-full object-cover">

                        <div class="absolute right-2 top-2 flex gap-1.5">
                            <a href="{{ route('dashboard.projects.edit', $project) }}" wire:navigate
                               class="flex h-8 w-8 items-center justify-center rounded-lg backdrop-blur transition hover:scale-110"
                               style="background-color: rgba(10,14,22,0.6); color: #fff;" title="Ubah">
                                <i class="fa-solid fa-pen text-[11px]"></i>
                            </a>

                            <button type="button" wire:click="confirmDelete('{{ $project->id }}')"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg backdrop-blur transition hover:scale-110"
                                    style="background-color: rgba(10,14,22,0.6); color: #fff;" title="Hapus">
                                <i class="fa-solid fa-trash text-[11px]"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="truncate text-sm font-medium" style="color: var(--ink);">{{ $project->name }}</h3>

                        <div class="prose-content fade-clip mt-1.5 max-h-12 text-xs">
                            {!! $project->description !!}
                        </div>

                        <div class="mt-auto flex flex-wrap gap-1.5 pt-3">
                            @foreach ($project->techStacks->take(4) as $tech)
                                <x-dash.badge tone="primary" :icon="$tech->icon">{{ $tech->name }}</x-dash.badge>
                            @endforeach

                            @if ($project->techStacks->count() > 4)
                                <x-dash.badge>+{{ $project->techStacks->count() - 4 }}</x-dash.badge>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($projects->hasPages())
            <div>{{ $projects->links() }}</div>
        @endif
    @endif

    <x-dash.confirm :show="$deletingId !== null" title="Hapus project ini?"
                    confirm="delete" cancel="cancelDelete" confirm-label="Ya, hapus">
        Project beserta gambarnya dihapus permanen dan langsung hilang dari landing page.
        Tindakan ini tidak bisa dibatalkan.
    </x-dash.confirm>
</div>
