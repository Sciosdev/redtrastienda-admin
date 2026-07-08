@php use App\Utils\Helpers; @endphp

@extends("auction.layouts.auction-app")

@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-5">
        <div class="container">
            <div class="row g-3">
                @include('theme-views.partials._profile-aside')
                <div class="col-lg-9">
                    <div class="card h-100">
                        <div class="card-body p-lg-4">
                            @yield('profile_content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
