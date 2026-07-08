@if(isset($endingSoonProducts) && count($endingSoonProducts) > 0)
<div class="ending-soon-section p-20 bg-gradient1 rounded overflow-hidden position-relative mb-20">
    <div class="section-card-margin">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-20">
            <h2 class="m-0 text-capitalize fw-bold fs-5 d-flex align-items-center gap-1">
                <img width="34" height="34" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/ending-soon.png') }}" alt="">
                <span class="line--limit-1">{{ translate('Ending Soon') }}</span>
            </h2>
            <div class="text-end">
                <a class="d-flex align-items-center text-nowrap gap-1 text-capitalize text-decoration-none fs-14 view-all-text text-primary" href="{{ route('auction.ending-soon-products') }}">
                    {{ translate('view_all')}}
                    <i class="fi fi-rr-angle-right fs-12"></i>
                </a>
            </div>
        </div>
        <div class="ending-soon-product position-relative">
            <div class="swiper-container">
                <div class="position-relative">
                    <div class="swiper" data-swiper-margin="16" data-swiper-autoplay="true"
                        data-swiper-pagination-el="null" data-swiper-navigation-next=".ending-nav-next"
                        data-swiper-navigation-prev=".ending-nav-prev"
                        data-swiper-breakpoints='{"0": {"slidesPerView": "1"}, "425": {"slidesPerView": "2"}, "992": {"slidesPerView": "3"}, "1200": {"slidesPerView": "4"}, "1400": {"slidesPerView": "4.2"}}'>
                        <div class="swiper-wrapper">
                            @foreach($endingSoonProducts as $auctionProduct)
                                <div class="swiper-slide">
                                    @include('auction.web-views.partials._auction-product-card', ['auctionProduct' => $auctionProduct])
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="swiper-button-next ending-nav-next"></div>
                    <div class="swiper-button-prev ending-nav-prev"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
