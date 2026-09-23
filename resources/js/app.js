// ============================================================
// Password Visibility Toggle
// Tujuan: Mengubah tipe input antara password dan teks serta mengganti ikon mata.
// ============================================================
window.togglePassword = function (inputId, button) {
    const input = document.getElementById(inputId);
    if (!input || !button) return;

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';

    button.innerHTML = isPassword
        ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.75 18.75 0 0 1 5.06-6.06"></path><path d="M1 1l22 22"></path><path d="M9.88 9.88a3 3 0 0 0 4.24 4.24"></path><path d="M14.12 14.12a3 3 0 0 1-4.24-4.24"></path></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
};

// ============================================================
// Vite HMR Cleanup
// Tujuan: Mencegah penumpukan duplicate listener saat Hot Module Replacement aktif.
// ============================================================
if (window.__appAbortController) {
    window.__appAbortController.abort();
}
window.__appAbortController = new AbortController();
const signal = window.__appAbortController.signal;

// ============================================================
// Sidebar Accordion
// Tujuan: Mengontrol buka-tutup dropdown menu sidebar secara efisien menggunakan Event Delegation.
// ============================================================
document.addEventListener('click', (e) => {
    const button = e.target.closest('[data-sidebar-toggle]');
    if (!button) return;

    const target = document.getElementById(button.dataset.target);
    if (!target) return;

    const isExpanded = button.getAttribute('aria-expanded') === 'true';

    if (isExpanded) {
        target.style.maxHeight = `${target.scrollHeight}px`;
        button.setAttribute('aria-expanded', 'false');
        requestAnimationFrame(() => {
            target.style.maxHeight = '0px';
        });
    } else {
        button.setAttribute('aria-expanded', 'true');
        target.style.maxHeight = `${target.scrollHeight}px`;
        target.addEventListener('transitionend', () => {
            if (button.getAttribute('aria-expanded') === 'true') {
                target.style.maxHeight = 'none';
            }
        }, { once: true });
    }
}, { signal });

// ============================================================
// Select2 Initialization
// Tujuan: Menginisialisasi dropdown Select2 jika library jQuery & Select2 tersedia.
// ============================================================
function initSelect2() {
    if (typeof jQuery !== 'undefined' && $.fn.select2) {
        $('.select2').each(function () {
            const $this = $(this);
            if ($this.data('select2')) return;
            $this.select2({
                width: '100%',
                placeholder: $this.data('placeholder') || 'Pilih opsi...',
                allowClear: Boolean($this.data('allow-clear'))
            });
        });
    }
}

// ============================================================
// Progress Bar Loader (NProgress-Style)
// Tujuan: Memberikan indikator loading visual halus di bagian atas layar saat navigasi.
// ============================================================
if (!window.ProgressBar) {
    window.ProgressBar = {
        status: null,
        timeout: null,
        elements: { 
            container: null, 
            bar: null 
        },

        create() {
            if (this.elements.container) return;

            const existing = document.getElementById('nprogress');
            if (existing) {
                this.elements.container = existing;
                this.elements.bar = existing.querySelector('.bar');
                return;
            }

            const container = document.createElement('div');
            container.id = 'nprogress';
            const bar = document.createElement('div');
            bar.className = 'bar';
            bar.setAttribute('role', 'bar');

            container.appendChild(bar);
            document.body.appendChild(container);

            this.elements.container = container;
            this.elements.bar = bar;
        },

        set(n) {
            this.create();
            n = Math.max(0, Math.min(1, n));
            this.status = n;

            if (this.elements.bar) {
                this.elements.bar.style.width = `${n * 100}%`;
                this.elements.bar.style.opacity = '1';
            }
        },

        start() {
            this.set(this.status && this.status < 1 ? this.status : 0);

            const work = () => {
                this.timeout = setTimeout(() => {
                    if (this.status === null || this.status >= 0.99) return;

                    const step = this.status < 0.2 ? 0.1
                        : this.status < 0.5 ? 0.04
                        : this.status < 0.8 ? 0.02
                        : 0.005;

                    this.set(this.status + step);
                    work();
                }, 200);
            };

            work();
        },

        done() {
            if (this.status === null) return;

            clearTimeout(this.timeout);
            this.set(1);

            setTimeout(() => {
                if (this.elements.bar) {
                    this.elements.bar.style.opacity = '0';
                }
                setTimeout(() => {
                    if (this.elements.bar) {
                        this.elements.bar.style.width = '0%';
                    }
                    this.status = null;
                }, 200);
            }, 300);
        }
    };
}

const ProgressBar = window.ProgressBar;

const isAdminPanel = () => Boolean(
    document.getElementById('admin-sidebar') || document.querySelector('[data-admin-panel="true"]')
);

// ============================================================
// Auto-Trigger Progress Bar Link Click
// Tujuan: Memicu animasi progress bar secara otomatis ketika link internal di-klik.
// ============================================================
document.addEventListener('click', (event) => {
    const link = event.target.closest('a');
    if (!link) return;

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    if (link.target === '_blank') return;

    const href = link.getAttribute('href');
    if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
        return;
    }

    if (!isAdminPanel()) return;

    try {
        const targetUrl = new URL(href, window.location.href);
        if (targetUrl.origin !== window.location.origin) return;

        if (targetUrl.pathname === window.location.pathname && targetUrl.search === window.location.search) {
            return;
        }
    } catch {
        return;
    }

    ProgressBar.start();
}, { signal });

function initProgressBar() {
    if (isAdminPanel()) {
        ProgressBar.start();
        setTimeout(() => ProgressBar.done(), 150);
    }
}

window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        ProgressBar.done();
    }
}, { signal });

// ============================================================
// Prevent Double Submit Form
// Tujuan: Mencegah duplikasi submit form dan menampilkan indikator loading pada tombol submit.
// ============================================================
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!form || form.classList.contains('no-prevent-double-submit')) return;

    if (form.dataset.submitting === 'true') {
        event.preventDefault();
        return;
    }
    form.dataset.submitting = 'true';

    const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    submitButtons.forEach((button) => {
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');

        const loadingText = button.getAttribute('data-loading-text') || 'Sending...';
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${loadingText}
        `;
    });
}, { signal });

// ============================================================
// Bootstrap Initialization
// Tujuan: Menjalankan setup awal komponen saat DOM selesai dimuat.
// ============================================================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initSelect2();
        initProgressBar();
    }, { once: true });
} else {
    initSelect2();
    initProgressBar();
}

// Vite HMR Support
if (import.meta.hot) {
    import.meta.hot.accept();
}

