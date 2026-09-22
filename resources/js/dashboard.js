/* -------------------------------------------------------------------------
 * Modul berat (Echo/Pusher, SweetAlert2, Chart.js) dimuat saat benar-benar
 * dipakai. Sebelumnya ketiganya ikut di bundel masuk setiap halaman dashboard
 * walau halaman itu tidak punya chart dan tidak memunculkan toast sama sekali.
 * ---------------------------------------------------------------------- */
if (import.meta.env.VITE_REVERB_APP_KEY) {
    import('./echo');
}

/* -------------------------------------------------------------------------
 * Token desain dibaca dari CSS custom property, sehingga toast dan chart
 * otomatis mengikuti warna serta tema yang dipilih di Pengaturan.
 * ---------------------------------------------------------------------- */
const token = (name, fallback = '') =>
    getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;

const isDark = () => document.documentElement.classList.contains('dark');

function syncStoredTheme() {
    const savedTheme = localStorage.getItem('color-theme');
    const shouldUseDark = savedTheme === 'dark'
        || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);

    document.documentElement.classList.toggle('dark', shouldUseDark);
}

function hexToRgba(hex, alpha) {
    const value = hex.replace('#', '').trim();

    if (value.length !== 6) return 'rgba(56, 189, 248, ' + alpha + ')';

    const int = parseInt(value, 16);
    const r = (int >> 16) & 255;
    const g = (int >> 8) & 255;
    const b = int & 255;

    return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
}

/* ---------------------------------- Toast -------------------------------- */

let toastPromise = null;

// SweetAlert2 baru diunduh saat toast pertama muncul; sesudah itu instance-nya
// dipakai ulang lewat promise yang sama.
function loadToast() {
    if (!toastPromise) {
        toastPromise = import('sweetalert2').then(({ default: Swal }) => Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3600,
            timerProgressBar: true,
            didOpen: (el) => {
                el.addEventListener('mouseenter', Swal.stopTimer);
                el.addEventListener('mouseleave', Swal.resumeTimer);
            },
        }));
    }

    return toastPromise;
}

window.dashToast = ({ type = 'success', message = '', title = null }) => {
    const iconColor =
        type === 'success' ? token('--success', '#10b981')
        : type === 'error' ? token('--danger', '#ef4444')
        : type === 'warning' ? token('--warning', '#f59e0b')
        : token('--primary', '#38bdf8');

    loadToast().then((toast) => toast.fire({
        icon: type,
        title: title || message,
        text: title ? message : undefined,
        background: token('--surface', '#ffffff'),
        color: token('--ink', '#1a1f2b'),
        iconColor,
    }));
};

/* ------------------------------ Tema gelap ------------------------------- */

function syncThemeIcons() {
    document.querySelectorAll('[data-theme-icon]').forEach((icon) => {
        icon.classList.toggle('fa-sun', isDark());
        icon.classList.toggle('fa-moon', !isDark());
    });
}

window.dashToggleTheme = () => {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('color-theme', isDark() ? 'dark' : 'light');
    syncThemeIcons();
    redrawCharts();
};

/* ---------------------------- Overlay muat ------------------------------- */

const overlay = () => document.getElementById('dash-loading');

let overlayDepth = 0;
let overlayTimer = null;

/**
 * Overlay dipakai untuk dua hal: perpindahan halaman wire:navigate dan aksi
 * yang memang lama (backup, unggah besar). Kemunculannya ditunda sesaat supaya
 * navigasi yang selesai seketika tidak menimbulkan kedipan.
 */
function showOverlay() {
    overlayDepth++;

    if (overlayTimer !== null) return;

    overlayTimer = setTimeout(() => {
        overlayTimer = null;

        if (overlayDepth > 0) overlay()?.classList.add('is-active');
    }, 140);
}

function hideOverlay(force = false) {
    overlayDepth = force ? 0 : Math.max(0, overlayDepth - 1);

    if (overlayDepth > 0) return;

    clearTimeout(overlayTimer);
    overlayTimer = null;

    overlay()?.classList.remove('is-active');
}

/* -------------------------------- Offline -------------------------------- */

function offlineOverlay() {
    let el = document.getElementById('dash-offline');

    if (!el) {
        const template = document.getElementById('dash-offline-template');

        el = document.createElement('div');
        el.id = 'dash-offline';
        el.style.cssText =
            'position:fixed;inset:0;z-index:120;display:none;align-items:center;justify-content:center;' +
            'background-color:var(--surface);';
        el.innerHTML = template ? template.innerHTML : '';

        document.body.appendChild(el);
    }

    return el;
}

function showOffline() {
    offlineOverlay().style.display = 'flex';
}

function hideOffline() {
    const el = document.getElementById('dash-offline');
    if (el) el.style.display = 'none';
}

window.addEventListener('offline', () => {
    showOffline();
    window.dashToast({ type: 'warning', message: 'Koneksi internet terputus.' });
});

window.addEventListener('online', () => {
    hideOffline();
    window.dashToast({ type: 'success', message: 'Koneksi kembali tersambung.' });
});

/* --------------------------------- Chart --------------------------------- */

const charts = new Map();

let chartPromise = null;

// Chart.js hanya relevan di Beranda dan Pengunjung. Halaman lain tidak perlu
// ikut mengunduhnya, jadi modulnya ditarik saat ada canvas yang memintanya.
function loadChart() {
    if (!chartPromise) {
        chartPromise = import('chart.js').then((module) => {
            module.Chart.register(
                module.LineController,
                module.LineElement,
                module.PointElement,
                module.LinearScale,
                module.CategoryScale,
                module.Filler,
                module.Tooltip,
            );

            return module.Chart;
        });
    }

    return chartPromise;
}

async function initCharts() {
    const canvases = document.querySelectorAll('canvas[data-chart]');

    if (canvases.length === 0) return;

    const Chart = await loadChart();

    canvases.forEach((canvas) => {
        const config = JSON.parse(canvas.dataset.chart);

        if (charts.has(canvas.id)) {
            charts.get(canvas.id).destroy();
        }

        // Canvas bisa saja sudah dilepas Livewire selama modulnya diunduh.
        if (!canvas.isConnected) return;

        const context = canvas.getContext('2d');
        const primary = token('--primary', '#38bdf8');

        // Gradien vertikal tipis di bawah garis — memberi bobot visual tanpa
        // blok warna pekat yang menutupi grid latar.
        const gradient = context.createLinearGradient(0, 0, 0, canvas.offsetHeight || 220);
        gradient.addColorStop(0, hexToRgba(primary, 0.28));
        gradient.addColorStop(1, hexToRgba(primary, 0));

        charts.set(canvas.id, new Chart(context, {
            type: 'line',
            data: {
                labels: config.labels,
                datasets: [{
                    data: config.data,
                    borderColor: primary,
                    backgroundColor: gradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: primary,
                    pointHoverBorderColor: token('--surface', '#ffffff'),
                    pointHoverBorderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0a0e16',
                        titleColor: '#ffffff',
                        bodyColor: '#cbd5e1',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: (item) => ' ' + item.formattedValue + ' pengunjung',
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: token('--ink-soft', '#5b6472'),
                            font: { size: 10, family: 'JetBrains Mono, monospace' },
                            maxRotation: 0,
                            autoSkipPadding: 16,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: { color: hexToRgba(token('--ink', '#1a1f2b'), 0.06) },
                        ticks: {
                            color: token('--ink-soft', '#5b6472'),
                            font: { size: 10, family: 'JetBrains Mono, monospace' },
                            precision: 0,
                            maxTicksLimit: 5,
                        },
                    },
                },
            },
        }));
    });
}

function redrawCharts() {
    charts.forEach((chart) => chart.destroy());
    charts.clear();
    initCharts();
}

/* ------------------------------- Count up -------------------------------- */

function initCountUp() {
    document.querySelectorAll('[data-countup]:not([data-counted])').forEach((el) => {
        const target = parseFloat(el.dataset.countup);
        if (isNaN(target)) return;

        el.dataset.counted = '1';

        const duration = 900;
        const start = performance.now();

        const step = (now) => {
            const ratio = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - ratio, 3);

            el.textContent = Math.floor(eased * target).toLocaleString('id-ID');

            if (ratio < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = target.toLocaleString('id-ID');
            }
        };

        requestAnimationFrame(step);
    });
}

/* --------------------------- Editor teks kaya ---------------------------- */

document.addEventListener('alpine:init', () => {
    /**
     * Shell dashboard. Dua state terpisah dengan sengaja:
     *  - `open`      : drawer mobile, selalu mulai tertutup.
     *  - `collapsed` : rail desktop, diingat antar kunjungan.
     * Sebelumnya keduanya digabung jadi satu boolean, sehingga tombol toggle
     * di desktop tidak berefek karena kelas `lg:translate-x-0` selalu menang.
     */
    /**
     * Tombol simpan per form: hanya muncul bila ada input di dalam form itu
     * yang nilainya berbeda dari saat halaman dimuat.
     *
     * Perbandingan dilakukan sepenuhnya di browser. Alternatifnya adalah
     * wire:model.live di setiap field, yang berarti satu request per ketikan.
     */
    window.Alpine.data('dashDirtyForm', () => ({
        dirty: false,
        snapshot: '',

        init() {
            this.$nextTick(() => { this.snapshot = this.serialize(); });

            // Satu listener di level form, bukan per input, supaya field yang
            // ditambahkan Livewire setelah render ikut terpantau.
            this.$el.addEventListener('input', () => this.check());
            this.$el.addEventListener('change', () => this.check());
        },

        fields() {
            return Array.from(this.$el.querySelectorAll('input, select, textarea'));
        },

        serialize() {
            return this.fields()
                .map((el) => (el.type === 'checkbox' || el.type === 'radio'
                    ? (el.checked ? '1' : '0')
                    : el.value))
                .join('\u0000');
        },

        check() {
            this.dirty = this.serialize() !== this.snapshot;
        },

        markClean() {
            // Tunggu Livewire selesai menukar DOM-nya sebelum mengambil
            // cuplikan baru, kalau tidak nilainya masih yang lama.
            this.$nextTick(() => {
                this.snapshot = this.serialize();
                this.dirty = false;
            });
        },

        revert() {
            const values = this.snapshot.split('\u0000');

            this.fields().forEach((el, index) => {
                const value = values[index];
                if (value === undefined) return;

                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = value === '1';
                } else {
                    el.value = value;
                }

                // Livewire hanya melihat perubahan yang mengirim event.
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            });

            this.dirty = false;
        },
    }));

    /**
     * Lonceng notifikasi. Reverb yang menyegarkan seketika; wire:poll di
     * markup tetap jalan sebagai cadangan bila soketnya tidak tersedia.
     *
     * Properti `realtime` di komponen Livewire dipakai untuk mengendurkan
     * interval polling selama soket benar-benar tersambung.
     */
    window.Alpine.data('dashNotificationBell', (userId) => ({
        open: false,

        init() {
            const echo = window.Echo;

            if (!echo || !userId) return;

            echo.private('App.Models.User.' + userId)
                .notification(() => this.$wire.$refresh());

            const socket = echo.connector?.pusher?.connection;

            if (!socket) return;

            const sync = () => this.$wire.set('realtime', socket.state === 'connected', true);

            ['connected', 'disconnected', 'unavailable', 'failed'].forEach((event) => {
                socket.bind(event, sync);
            });

            sync();
        },
    }));

    window.Alpine.data('dashShell', () => ({
        open: false,
        collapsed: localStorage.getItem('dash-sidebar-collapsed') === '1',

        init() {
            // Tutup drawer saat berpindah halaman atau membesar ke desktop.
            document.addEventListener('livewire:navigated', () => { this.open = false; });

            this.onResize = () => { if (window.innerWidth >= 1024) this.open = false; };
            window.addEventListener('resize', this.onResize);
        },

        destroy() {
            window.removeEventListener('resize', this.onResize);
        },

        /**
         * Satu tombol untuk dua perilaku: di bawah lg sidebar adalah drawer
         * yang dibuka/ditutup, di atasnya rail yang diciutkan/dilebarkan.
         */
        toggleSidebar() {
            if (window.innerWidth >= 1024) {
                this.toggleDesktop();

                return;
            }

            this.open = !this.open;
        },

        /**
         * Lebarnya diurus CSS lewat kelas .is-collapsed; di sini cukup
         * menyimpan pilihannya supaya bertahan antar halaman.
         */
        toggleDesktop() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('dash-sidebar-collapsed', this.collapsed ? '1' : '0');
        },
    }));

    window.Alpine.data('dashRichEditor', (initial = '') => ({
        content: initial,

        init() {
            this.$refs.editor.innerHTML = initial || '';

            // Server bisa mengubah properti ini (mis. setelah form di-reset);
            // sinkronkan balik ke DOM tanpa mengganggu kursor saat mengetik.
            this.$watch('content', (value) => {
                if (value !== this.$refs.editor.innerHTML) {
                    this.$refs.editor.innerHTML = value || '';
                }
            });
        },

        run(command) {
            const parts = command.split(':');
            const name = parts[0];
            const argument = parts[1];

            if (name === 'createLink') {
                const url = window.prompt('Masukkan URL (http/https):', 'https://');

                // Skema selain http/https ditolak di sini; server tetap
                // menyaring ulang lewat SanitizeRichText saat disimpan.
                if (!url || !/^https?:\/\//i.test(url)) return;

                document.execCommand('createLink', false, url);
                this.sync();

                return;
            }

            document.execCommand(name, false, argument || null);
            this.$refs.editor.focus();
            this.sync();
        },

        sync() {
            this.content = this.$refs.editor.innerHTML;
        },
    }));
});

/* ------------------------------- Livewire -------------------------------- */

document.addEventListener('livewire:init', () => {
    Livewire.on('toast', (payload) => {
        window.dashToast(Array.isArray(payload) ? payload[0] : payload);
    });

    // Ganti warna aksen seketika setelah Pengaturan disimpan, tanpa reload.
    Livewire.on('settings-saved', (payload) => {
        const data = Array.isArray(payload) ? payload[0] : payload;

        if (data && data.color) {
            document.documentElement.style.setProperty('--primary', data.color);
            redrawCharts();
        }
    });

    Livewire.hook('commit', ({ component, respond, succeed, fail }) => {
        // Tombol yang memicu request ini ditandai sibuk sampai jawabannya
        // datang, lalu dilepas. Satu tempat untuk seluruh dashboard, jadi
        // tidak ada tombol yang terlewat.
        //
        // Tombolnya harus berada di dalam komponen yang mengirim commit ini,
        // supaya request latar (wire:poll lonceng) tidak menandai tombol yang
        // kebetulan baru saja diklik di komponen lain.
        const trigger = pendingTrigger;

        if (trigger && component.el?.contains(trigger)) {
            pendingTrigger = null;

            markBusy(trigger);

            const done = () => clearBusy(trigger);

            respond(done);
            succeed(done);
            fail(done);
        }

        // Overlay penuh hanya untuk aksi yang memang lama (unggah besar,
        // backup). Penandanya adalah elemen yang benar-benar diklik, bukan
        // keberadaan atribut di mana pun dalam komponen — supaya tombol
        // "Batal" di dialog yang sama tidak ikut memunculkan overlay.
        if (!blockingClicked) return;

        blockingClicked = false;

        showOverlay();
        succeed(() => hideOverlay());
        fail(() => hideOverlay());
    });
});

/* --------------------------- Tombol sedang sibuk -------------------------- */

let blockingClicked = false;
let pendingTrigger = null;

const hasWireAttribute = (el, prefix) =>
    Array.from(el.attributes).some((attribute) => attribute.name.startsWith(prefix));

const busyTimers = new WeakMap();

function markBusy(el) {
    if (el.classList.contains('dash-busy')) return;

    // Warna spinner dibaca sebelum teks tombol dibuat transparan, supaya
    // cincinnya tetap kontras di tombol primary maupun secondary.
    el.style.setProperty('--dash-busy-ink', getComputedStyle(el).color);
    el.classList.add('dash-busy');

    // Jaring pengaman: commit yang dibatalkan tidak memanggil balik apa pun,
    // dan tombol yang terkunci selamanya jauh lebih buruk dari spinner hilang.
    busyTimers.set(el, setTimeout(() => clearBusy(el), 20000));
}

function clearBusy(el) {
    clearTimeout(busyTimers.get(el));
    busyTimers.delete(el);

    el.classList.remove('dash-busy');
    el.style.removeProperty('--dash-busy-ink');
}

/**
 * Elemen yang layak diberi status sibuk: tombol dengan wire:click, atau tombol
 * submit milik form wire:submit. Tombol yang sudah punya indikator sendiri
 * (wire:loading di dalamnya) dilewati supaya tidak ada dua spinner.
 */
function findTrigger(target) {
    const el = target.closest('button, [role="tab"]');

    if (!el || el.disabled || el.querySelector('[wire\:loading]')) return null;

    if (hasWireAttribute(el, 'wire:click')) return el;

    const form = el.form ?? el.closest('form');

    if (el.type === 'submit' && form && hasWireAttribute(form, 'wire:submit')) return el;

    return null;
}

function rememberTrigger(el) {
    pendingTrigger = el;

    // Klik yang ternyata tidak memicu request apa pun tidak boleh menempel dan
    // ikut menandai commit berikutnya. Jedanya harus lebih panjang dari 5ms
    // buffer Livewire, kalau tidak penandanya hilang sebelum commit dibuat.
    setTimeout(() => {
        if (pendingTrigger === el) pendingTrigger = null;
    }, 120);
}

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) return;

    if (event.target.closest('[data-dash-blocking]')) {
        blockingClicked = true;
    }

    const trigger = findTrigger(event.target);

    if (trigger) rememberTrigger(trigger);
}, true);

// Submit lewat tombol Enter tidak melewati handler klik di atas.
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !hasWireAttribute(form, 'wire:submit')) return;

    const button = form.querySelector('button[type="submit"]:not([disabled])');

    if (button && !button.querySelector('[wire\:loading]')) rememberTrigger(button);
}, true);

document.addEventListener('livewire:navigate', () => showOverlay());

document.addEventListener('livewire:navigated', () => {
    // Navigasi selesai: tutup paksa, karena halaman baru membawa DOM baru dan
    // penghitung dari halaman lama tidak lagi relevan.
    hideOverlay(true);
    syncStoredTheme();
    syncThemeIcons();
    initCharts();
    initCountUp();
});

document.addEventListener('DOMContentLoaded', () => {
    syncStoredTheme();
    syncThemeIcons();
    initCharts();
    initCountUp();

    if (!navigator.onLine) showOffline();
});

/* ------------------------------- reCAPTCHA ------------------------------- */

document.addEventListener('alpine:init', () => {
    /**
     * reCAPTCHA v3 tidak punya tantangan visual: token diambil diam-diam tepat
     * sebelum submit. Skrip Google hanya dimuat bila reCAPTCHA benar-benar
     * aktif, jadi halaman login tetap ringan saat fitur ini dimatikan.
     */
    window.Alpine.data('dashRecaptcha', (siteKey) => ({
        ready: false,

        load() {
            if (document.getElementById('recaptcha-script')) {
                // Tag-nya sudah ada, tapi belum tentu selesai dimuat.
                this.ready = typeof window.grecaptcha !== 'undefined';
                this.watchSubmit();

                return;
            }

            const script = document.createElement('script');
            script.id = 'recaptcha-script';
            script.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(siteKey);
            script.async = true;
            script.defer = true;
            script.onload = () => { this.ready = true; };
            // Skrip Google diblokir atau jaringan putus. Dicatat supaya kegagalan
            // ini tidak menyamar sebagai form yang diam tanpa sebab.
            script.onerror = () => {
                this.ready = false;
                console.warn('reCAPTCHA: skrip Google tidak dapat dimuat.');
            };

            document.head.appendChild(script);
            this.watchSubmit();
        },

        watchSubmit() {
            // Submit ditahan sampai token terisi, lalu dilanjutkan sekali.
            this.$el.addEventListener('submit', (event) => {
                if (this.$el.dataset.tokenReady === '1') {
                    delete this.$el.dataset.tokenReady;

                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                this.fetchToken().finally(() => {
                    this.$el.dataset.tokenReady = '1';
                    this.$el.requestSubmit();
                });
            }, true);
        },

        fetchToken() {
            if (!this.ready || typeof window.grecaptcha === 'undefined') {
                // Skrip Google tidak termuat (diblokir/offline). Dibiarkan
                // kosong: server memutuskan, dan throttle login tetap berlaku.
                return Promise.resolve();
            }

            return new Promise((resolve) => {
                // Apa pun yang terjadi di bawah, submit harus tetap dilanjutkan.
                // Promise yang menggantung akan membuat tombol Masuk diam total.
                const timer = setTimeout(resolve, 8000);
                const done = () => { clearTimeout(timer); resolve(); };

                try {
                    window.grecaptcha.ready(() => {
                        try {
                            // grecaptcha.execute() mengembalikan thenable miliknya
                            // sendiri, bukan Promise asli: ada .then() tapi tidak ada
                            // .finally(). Dibungkus Promise.resolve() supaya rantainya
                            // kembali jadi promise standar.
                            Promise.resolve(window.grecaptcha.execute(siteKey, { action: 'login' }))
                                .then((token) => this.$wire.set('recaptchaToken', token, false))
                                .catch(() => {})
                                .finally(done);
                        } catch (error) {
                            done();
                        }
                    });
                } catch (error) {
                    done();
                }
            });
        },
    }));
});

document.addEventListener('livewire:init', () => {
    Livewire.on('recaptcha-reset', () => {
        document.querySelectorAll('form[data-token-ready]').forEach((form) => {
            delete form.dataset.tokenReady;
        });
    });
});
