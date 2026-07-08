@extends("auction.layouts.auction-app")

@section('title', 'Search Page')

@section('content')
<div class="container py-4">

    @include("auction.web-views.partials._product-list-header")
    <div>
        <div class="row g-3">
            <div class="col-lg-3">
                <div class="search-filer-sidebar rounded bg-white border d-lg-block d-none ">
                    <h5 class="fs-16 fw-semibold title-clr py-12px px-10px border-bottom mb-20">{{ translate('Filter_By') }}</h5>
                    @include("auction.web-views.partials._search-filter-sidebar")
                </div>
            </div>
            <div class="col-lg-9">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-15">
                    <h5 class="mb-0 text-clr fw-semibold">{{ translate('All Auctions') }}</h5>
                    <ul class="nav d-flex align-items-center gap-lg-4 gap-3 tabs-grid-list" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link btn outline-0 p-0 border-0 d-flex align-items-center gap-1 fs-12 active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">
                                <i class="fi fi-rr-apps"></i> {{ translate('Grid view') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link btn outline-0 p-0 border-0 d-flex align-items-center gap-1 fs-12" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">
                                <i class="fi fi-rr-list"></i> {{ translate('List view') }}
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                        <div class="row g-sm-3 g-2">
                            <div class="col-md-6">
                                @include("auction.web-views.partials._auction-product-horizontal")
                            </div>
                            <div class="col-md-6">
                                @include("auction.web-views.partials._auction-product-horizontal")
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                        <div class="row g-sm-3 g-2">
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                @include("auction.web-views.partials._auction-product-horizontal-grid")
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                @include("auction.web-views.partials._auction-product-horizontal-grid")
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                @include("auction.web-views.partials._auction-product-horizontal-grid")
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                @include("auction.web-views.partials._auction-product-horizontal-grid")
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
