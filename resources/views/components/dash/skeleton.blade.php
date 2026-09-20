@props(['rows' => 5, 'target' => null])

<div @if ($target) wire:loading.delay.class.remove="hidden" wire:target="{{ $target }}" class="hidden" @endif>
    <div class="space-y-3 p-5">
        @for ($i = 0; $i < $rows; $i++)
            <div class="flex items-center gap-4">
                <div class="dash-skeleton h-9 w-9 shrink-0 rounded-xl"></div>
                <div class="flex-1 space-y-2">
                    <div class="dash-skeleton h-3" style="width: {{ [70, 55, 82, 64, 48][$i % 5] }}%"></div>
                    <div class="dash-skeleton h-2.5" style="width: {{ [40, 60, 35, 52, 44][$i % 5] }}%"></div>
                </div>
            </div>
        @endfor
    </div>
</div>
