@extends('layouts.base')

@section('body')
    @include('partials.public-nav')
    <main>
        @yield('content')
    </main>
@endsection
