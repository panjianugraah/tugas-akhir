@extends('layouts.public')

@section('title', 'SMP Angkasa Adi Soemarmo')

@section('content')
    @include('partials.hero')
    @include('partials.about')
    @include('partials.vision-mission')
    @include('partials.programs')
    @include('partials.gallery-section')

    <section id="location" class="section section--soft section-anchor">
        <div class="container container--narrow text-center">
            <h2 class="section-title">Lokasi Sekolah</h2>

            <p class="mt-4 mb-6 muted">
                Temukan lokasi SMP Angkasa Adi Soemarmo dan petunjuk perjalanan melalui Google Maps.
            </p>

            <iframe
                title="Lokasi SMP Angkasa Adi Soemarmo"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d349.60827299237013!2d110.74650029148995!3d-7.534991260575801!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a149cea60e445%3A0xd79c0e33870074f5!2sSMP%20Angkasa!5e0!3m2!1sid!2smy!4v1792588110664!5m2!1sid!2smy"
                width="100%"
                height="350"
                class="mt-8"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                frameborder="0"
            ></iframe>
        </div>
    </section>
@endsection
