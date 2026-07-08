@if(isset($auctionRecentView) && count($auctionRecentView) > 0)
<div class="recently-viewed-section p-20 pe-xl-0 rounded overflow-hidden position-relative mb-20">
    <div class="section-card-margin">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3 pe-xl-3">
            <h2 class="m-0 text-capitalize fw-bold fs-5 d-flex align-items-center gap-1">
                <span class="line--limit-1">{{ translate('Recently Viewed') }}</span>
            </h2>
            @if(auth('customer')->check())
            <div class="text-end">
                <a class="d-flex align-items-center text-nowrap gap-1 text-capitalize text-decoration-none fs-14 view-all-text title-clr"
                   href="{{ route('auction.products.recent') }}">
                    {{ translate('view_all')}}
                    <i class="fi fi-rr-angle-right fs-12 text-primary"></i>
                </a>
            </div>
            @endif
        </div>
        <div class="recently-viewed-product position-relative">
            <div class="swiper-container">
                <div class="position-relative">
                    <div class="swiper" data-swiper-margin="16"
                        data-swiper-pagination-el="null" data-swiper-navigation-next=".recently-nav-next"
                        data-swiper-navigation-prev=".recently-nav-prev"
                        data-swiper-breakpoints='{"0": {"slidesPerView": "1"}, "470": {"slidesPerView": "1.5"}, "992": {"slidesPerView": "2"}, "1200": {"slidesPerView": "2.6"}, "1400": {"slidesPerView": "2.6"}}'>
                        <div class="swiper-wrapper">
                            @foreach($auctionRecentView as $auctionProduct)
                                <div class="swiper-slide">
                                    @include('auction.web-views.partials._auction-product-horizontal-thin', ['auctionProduct' => $auctionProduct])
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="swiper-button-next recently-nav-next"></div>
                    <div class="swiper-button-prev recently-nav-prev"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
