import { useState, useEffect, useCallback } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

const heroSlides = [
    {
        image: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
        title: 'Selamat Datang di SMP Angkasa Adi Soemarmo',
        subtitle: 'Tempat belajar, bertumbuh, dan meraih cita-cita',
    },
    {
        image: 'https://images.unsplash.com/photo-1573894998033-c0cef4ed722b?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
        title: 'Pendidikan untuk Masa Depan',
        subtitle: 'Mengembangkan potensi siswa menjadi generasi berilmu, berkarakter, dan berprestasi',
    },
    {
        image: 'https://images.unsplash.com/photo-1544776193-352d25ca82cd?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080',
        title: 'Bersama Guru, Menggapai Cita-Cita',
        subtitle: 'Mendampingi setiap langkah siswa dalam belajar dan mengembangkan kemampuan',
    },
];

export default function HeroCarousel() {
  const [current, setCurrent] = useState(0);
  const [animating, setAnimating] = useState(false);

  const goTo = useCallback(
    (index : number) => {
      if (animating) return;
      setAnimating(true);
      setCurrent((index + heroSlides.length) % heroSlides.length);
      setTimeout(() => setAnimating(false), 600);
    },
    [animating],
  );

  const next = useCallback(() => goTo(current + 1), [current, goTo]);
  const prev = useCallback(() => goTo(current - 1), [current, goTo]);

  // Auto-slide setiap 4 detik
  useEffect(() => {
    const timer = setInterval(next, 4000);
    return () => clearInterval(timer);
  }, [next]);

  return (
    <div id="home" className="relative overflow-hidden h-[500px] md:h-[600px] bg-gray-900">
      {/* Slides */}
      {heroSlides.map((slide, index) => (
        <div
          key={index}
          className={`absolute inset-0 transition-opacity duration-700 ease-in-out ${
            index === current ? 'opacity-100 z-10' : 'opacity-0 z-0'
          }`}
        >
          <img src={slide.image} alt={slide.title} className="w-full h-full object-cover" />
          <div className="absolute inset-0 bg-black/45 flex items-center justify-center">
            <div
              className={`text-center text-white px-6 transition-all duration-700 ${
                index === current ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'
              }`}
            >
              <h2 className="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">{slide.title}</h2>
              <p className="text-xl md:text-2xl text-white/90 drop-shadow">{slide.subtitle}</p>
            </div>
          </div>
        </div>
      ))}

      {/* Tombol Prev */}
      <button
        onClick={prev}
        aria-label="Previous slide"
        className="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/30 hover:bg-black/60 text-white flex items-center justify-center transition backdrop-blur-sm"
      >
        <ChevronLeft size={22} />
      </button>

      {/* Tombol Next */}
      <button
        onClick={next}
        aria-label="Next slide"
        className="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/30 hover:bg-black/60 text-white flex items-center justify-center transition backdrop-blur-sm"
      >
        <ChevronRight size={22} />
      </button>

      {/* Dot indicator — bisa diklik langsung ke slide tertentu */}
      <div className="absolute bottom-5 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
        {heroSlides.map((_, index) => (
          <button
            key={index}
            onClick={() => goTo(index)}
            aria-label={`Go to slide ${index + 1}`}
            className={`transition-all duration-300 rounded-full ${
              index === current ? 'w-6 h-2.5 bg-white' : 'w-2.5 h-2.5 bg-white/50 hover:bg-white/80'
            }`}
          />
        ))}
      </div>
    </div>
  );
}