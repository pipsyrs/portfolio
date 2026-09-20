{{-- Ditampilkan sebagai overlay oleh dashboard.js saat koneksi terputus. --}}
<div class="flex flex-col items-center justify-center px-6 text-center">
    <span class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl"
          style="background-color: color-mix(in srgb, var(--warning) 14%, transparent); color: var(--warning);">
        <i class="fa-solid fa-wifi text-xl"></i>
    </span>

    <h1 class="text-lg font-bold" style="color: var(--ink);">Koneksi terputus</h1>
    <p class="mt-2 max-w-sm text-sm leading-relaxed" style="color: var(--ink-soft);">
        Perubahan yang belum tersimpan masih ada di halaman ini. Sambungkan kembali
        internet, lalu coba simpan ulang.
    </p>

    <p class="mono mt-6 text-[11px]" style="color: var(--ink-soft);">
        MENUNGGU KONEKSI<span class="dash-dots"></span>
    </p>
</div>
