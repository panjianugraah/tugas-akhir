import HeroCarousel from '../pages/HeroCarousel';
import About from './about';
import VisionMission from '../pages/visionmission';
import Programs from '../pages/programs';
import Gallery from './gallery';
import Navbar from '../pages/navbar';

export default function Welcome() {
    return (
        <>
            <Navbar />
            <HeroCarousel />
            <About />
            <VisionMission />
            <Programs />
            <Gallery />
            <section id="location" className="bg-gray-50 py-16">
                <div className="mx-auto max-w-5xl px-4 text-center">
                    <h2 className="mb-4 text-3xl font-bold text-gray-900">
                        Lokasi Sekolah
                    </h2>

                    <p className="mx-auto mb-6 max-w-2xl text-gray-600">
                        Temukan lokasi SMP Angkasa Adi Soemarmo dan
                        petunjuk perjalanan melalui Google Maps.
                    </p>

                    <iframe
                        title="Lokasi SMP Angkasa Adi Soemarmo"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d349.60827299237013!2d110.74650029148995!3d-7.534991260575801!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a149cea60e445%3A0xd79c0e33870074f5!2sSMP%20Angkasa!5e0!3m2!1sid!2smy!4v1792588110664!5m2!1sid!2smy"
                        width="100%"
                        height="350"
                        className="mt-8 rounded-xl border-0"
                        loading="lazy"
                        allowFullScreen
                        referrerPolicy="no-referrer-when-downgrade"
                    />
                </div>
            </section>
        </>
    );
}
