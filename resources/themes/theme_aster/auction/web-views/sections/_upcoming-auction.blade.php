@if(isset($upcomingAuctionProducts) && count($upcomingAuctionProducts) > 0)
<div class="upcoming-section rounded overflow-hidden position-relative mb-20">
    <div class="section-card-margin">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-20">
            <h2 class="m-0 text-capitalize fw-bold fs-5 d-flex align-items-center gap-1">
                <span class="line--limit-1">
                    {{ translate('Upcoming Auction') }}
                </span>
            </h2>
            <div class="text-end">
                <a class="d-flex align-items-center text-nowrap gap-1 text-capitalize text-decoration-none fs-14 view-all-text title-clr" href="{{ route('auction.upcoming-products') }}">
                    {{ translate('view_all')}}
                    <i class="fi fi-rr-angle-right fs-12 text-primary"></i>
                </a>
            </div>
        </div>
        <div class="row g-xxl-3 g-lg-3 g-2">
            <div class="col-xxl-2 col-sm-2 d-sm-block d-none">
                <div class="upcomings-badge-thumb h-100 d-center rounded overflow-hidden">
                    <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/coming-soon2.png') }}" alt="" class="w-100 h-100 object-cover">
                </div>
            </div>
            <div class="col-xxl-10 col-sm-10">
                <div class="upcoming-soon-product position-relative">
                    <div class="swiper-container">
                        <div class="position-relative">
                            <div class="swiper" data-swiper-margin="16"
                                data-swiper-loop="{{ count($upcomingAuctionProducts) > 4 ? 'true' : 'false' }}"
                                data-swiper-pagination-el="null" data-swiper-navigation-next=".upcoming-nav-next"
                                data-swiper-navigation-prev=".upcoming-nav-prev"
                                data-swiper-breakpoints='{"0": {"slidesPerView": "1"}, "420": {"slidesPerView": "2"}, "992": {"slidesPerView": "3"}, "1200": {"slidesPerView": "4"}, "1400": {"slidesPerView": "4.2"}}'>
                                <div class="swiper-wrapper align-items-baseline">
                                    @foreach($upcomingAuctionProducts as $auctionProduct)
                                        <div class="swiper-slide">
                                            @include('auction.web-views.partials._auction-product-card', ['auctionProduct' => $auctionProduct])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="swiper-button-next upcoming-nav-next"></div>
                            <div class="swiper-button-prev upcoming-nav-prev"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
