@if(isset($similarAuctions) && $similarAuctions->isNotEmpty())
<div class="similar-soon-section p-20 bg-white shadow-sm rounded overflow-hidden position-relative mb-20">
    <div class="section-card-margin">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-20">
            <h2 class="m-0 text-capitalize fw-bold fs-5 d-flex align-items-center gap-1">
                <span class="line--limit-1">{{ translate('Similar Auctions') }}</span>
            </h2>
            <div class="text-end">
                <a class="d-flex align-items-center text-nowrap gap-1 text-capitalize text-decoration-none fs-14 view-all-text text-primary"
                   href="{{ isset($auctionCategory) ? route('auction.category-products', ['slug' => $auctionCategory?->slug]) : route('auction.search-page') }}">
                    {{ translate('view_all') }}
                    <i class="fi fi-rr-angle-right fs-12"></i>
                </a>
            </div>
        </div>
        <div class="ending-soon-product position-relative">
            <div class="swiper-container">
                <div class="position-relative">
                    <div class="swiper" data-swiper-margin="16"
                         data-swiper-pagination-el="null" data-swiper-navigation-next=".similar-nav-next"
                         data-swiper-navigation-prev=".similar-nav-prev"
                         data-swiper-breakpoints='{"0": {"slidesPerView": "1"}, "425": {"slidesPerView": "2"}, "992": {"slidesPerView": "3"}, "1200": {"slidesPerView": "4"}, "1400": {"slidesPerView": "5"}}'>
                        <div class="swiper-wrapper">
                            @foreach($similarAuctions as $auctionProduct)
                                <div class="swiper-slide pb-1">
                                    @include('auction.web-views.partials._auction-product-card', ['auctionProduct' => $auctionProduct])
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="swiper-button-next similar-nav-next"></div>
                    <div class="swiper-button-prev similar-nav-prev"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
