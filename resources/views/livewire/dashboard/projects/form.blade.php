<div class="mx-auto max-w-3xl space-y-6">

    <div>
        <a href="{{ route('dashboard.projects') }}" wire:navigate
           class="mono mb-3 inline-flex items-center gap-1.5 text-[11px] transition hover:underline"
           style="color: var(--ink-soft);">
            <i class="fa-solid fa-arrow-left text-[10px]"></i> KEMBALI
        </a>

        <h2 class="text-xl font-medium tracking-tight" style="color: var(--ink);">
            {{ $projectId ? 'Ubah Project' : 'Project Baru' }}
        </h2>
    </div>

    <form wire:submit="save" class="space-y-6">

        <x-dash.card title="Informasi Project" icon="fa-solid fa-circle-info">
            <div class="space-y-4">
                <x-dash.input label="Nama Project" name="name" required
                              wire:model="name" placeholder="Sistem Informasi Akademik" />

                <x-dash.rich-editor label="Deskripsi" name="description" :value="$description" required
                                    hint="Mendukung tebal, miring, tautan, dan daftar." />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-dash.input label="URL Project" name="url" type="url" required
                                  wire:model="url" placeholder="https://contoh.com" />

                    <x-dash.input label="Link GitHub" name="github_link" type="url"
                                  wire:model="github_link" placeholder="https://github.com/user/repo" />
                </div>
            </div>
        </x-dash.card>

        <x-dash.card title="Gambar" icon="fa-solid fa-image">
            <x-dash.file-drop
                name="image"
                wire:model="image"
                :current="$image ? $image->temporaryUrl() : ($currentImage ? safe_image_url($currentImage, 'projects') : null)"
                hint="JPG, PNG, WebP, atau GIF. Maksimal 2 MB, sisi terpanjang 4000px."
                accept="image/jpeg,image/png,image/webp,image/gif" />
        </x-dash.card>

        <x-dash.card title="Klasifikasi" icon="fa-solid fa-tags"
                     subtitle="Dipakai sebagai filter pada bagian Projects di landing page.">
            <div class="space-y-5">
                <div>
                    <label class="dash-label">Tech Stack <span style="color: var(--danger);">*</span></label>

                    <div class="flex flex-wrap gap-2">
                        @forelse ($techStacks as $tech)
                            <label wire:key="tech-{{ $tech->id }}"
                                   class="cursor-pointer select-none">
                                <input type="checkbox" value="{{ $tech->id }}" wire:model="techStackIds" class="peer sr-only">
                                <span class="dash-badge px-3 py-1.5 text-xs transition peer-checked:border-[var(--primary)] peer-checked:bg-[color-mix(in_srgb,var(--primary)_14%,transparent)] peer-checked:text-[var(--primary)]"
                                      style="border-color: var(--hairline); color: var(--ink-soft);">
                                    @if ($tech->icon)<i class="{{ $tech->icon }} text-[10px]"></i>@endif
                                    {{ $tech->name }}
                                </span>
                            </label>
                        @empty
                            <p class="text-xs" style="color: var(--ink-soft);">
                                Belum ada tech stack.
                                <a href="{{ route('dashboard.tech-stacks') }}" wire:navigate class="underline" style="color: var(--primary);">Tambah dulu</a>.
                            </p>
                        @endforelse
                    </div>

                    @error('techStackIds')
                        <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="dash-label">Spesialisasi <span style="color: var(--danger);">*</span></label>

                    <div class="flex flex-wrap gap-2">
                        @forelse ($specializations as $specialization)
                            <label wire:key="spec-{{ $specialization->id }}" class="cursor-pointer select-none">
                                <input type="checkbox" value="{{ $specialization->id }}" wire:model="specializationIds" class="peer sr-only">
                                <span class="dash-badge px-3 py-1.5 text-xs transition peer-checked:border-[var(--primary)] peer-checked:bg-[color-mix(in_srgb,var(--primary)_14%,transparent)] peer-checked:text-[var(--primary)]"
                                      style="border-color: var(--hairline); color: var(--ink-soft);">
                                    @if ($specialization->icon)<i class="{{ $specialization->icon }} text-[10px]"></i>@endif
                                    {{ $specialization->name }}
                                </span>
                            </label>
                        @empty
                            <p class="text-xs" style="color: var(--ink-soft);">
                                Belum ada spesialisasi.
                                <a href="{{ route('dashboard.specializations') }}" wire:navigate class="underline" style="color: var(--primary);">Tambah dulu</a>.
                            </p>
                        @endforelse
                    </div>

                    @error('specializationIds')
                        <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-dash.card>

        <div class="flex items-center justify-end gap-3">
            <x-dash.button href="{{ route('dashboard.projects') }}" wire:navigate variant="secondary">
                Batal
            </x-dash.button>

            <x-dash.button type="submit" loading-target="save" icon="fa-solid fa-floppy-disk" data-dash-blocking>
                {{ $projectId ? 'Simpan Perubahan' : 'Simpan Project' }}
            </x-dash.button>
        </div>
    </form>
</div>
