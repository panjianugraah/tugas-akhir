<section id="home" class="hero section-anchor" aria-label="Beranda">
    <div class="hero__slide is-active">
        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080" alt="Selamat Datang di SMP Angkasa Adi Soemarmo" />
        <div class="hero__overlay">
            <div class="hero__content">
                <h2 class="hero__title">Selamat Datang di SMP Angkasa Adi Soemarmo</h2>
                <p class="hero__subtitle">Tempat belajar, bertumbuh, dan meraih cita-cita</p>
            </div>
        </div>
    </div>

    <div class="hero__slide">
        <img src="https://images.unsplash.com/photo-1573894998033-c0cef4ed722b?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080" alt="Pendidikan untuk Masa Depan" />
        <div class="hero__overlay">
            <div class="hero__content">
                <h2 class="hero__title">Pendidikan untuk Masa Depan</h2>
                <p class="hero__subtitle">Mengembangkan potensi siswa menjadi generasi berilmu, berkarakter, dan berprestasi</p>
            </div>
        </div>
    </div>

    <div class="hero__slide">
        <img src="https://images.unsplash.com/photo-1544776193-352d25ca82cd?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080" alt="Bersama Guru, Menggapai Cita-Cita" />
        <div class="hero__overlay">
            <div class="hero__content">
                <h2 class="hero__title">Bersama Guru, Menggapai Cita-Cita</h2>
                <p class="hero__subtitle">Mendampingi setiap langkah siswa dalam belajar dan mengembangkan kemampuan</p>
            </div>
        </div>
    </div>

    <button type="button" class="hero__arrow hero__arrow--prev" data-hero-prev aria-label="Previous slide">
        <x-icon name="chevron-left" />
    </button>
    <button type="button" class="hero__arrow hero__arrow--next" data-hero-next aria-label="Next slide">
        <x-icon name="chevron-right" />
    </button>

    <div class="hero__dots">
        <button type="button" class="hero__dot is-active" data-hero-dot="0" aria-label="Go to slide 1" aria-current="true"></button>
        <button type="button" class="hero__dot" data-hero-dot="1" aria-label="Go to slide 2"></button>
        <button type="button" class="hero__dot" data-hero-dot="2" aria-label="Go to slide 3"></button>
    </div>
</section>

@push('scripts')
    <script>
        (function () {
            const root = document.querySelector('.hero');
            if (!root) return;

            const slides = Array.from(root.querySelectorAll('.hero__slide'));
            const dots = Array.from(root.querySelectorAll('.hero__dot'));
            const prev = root.querySelector('[data-hero-prev]');
            const next = root.querySelector('[data-hero-next]');
            if (!slides.length) return;

            let current = 0;
            let animating = false;
            let timer = null;

            function goTo(index) {
                if (animating) return;
                animating = true;
                current = ((index % slides.length) + slides.length) % slides.length;

                slides.forEach((slide, i) => {
                    slide.classList.toggle('is-active', i === current);
                });

                dots.forEach((dot, i) => {
                    const active = i === current;
                    dot.classList.toggle('is-active', active);
                    if (active) {
                        dot.setAttribute('aria-current', 'true');
                    } else {
                        dot.removeAttribute('aria-current');
                    }
                });

                window.setTimeout(() => {
                    animating = false;
                }, 600);
            }

            function start() {
                window.clearInterval(timer);
                timer = window.setInterval(() => goTo(current + 1), 4000);
            }

            if (prev) {
                prev.addEventListener('click', () => {
                    goTo(current - 1);
                    start();
                });
            }

            if (next) {
                next.addEventListener('click', () => {
                    goTo(current + 1);
                    start();
                });
            }

            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    goTo(index);
                    start();
                });
            });

            start();
        })();
    </script>
@endpush
