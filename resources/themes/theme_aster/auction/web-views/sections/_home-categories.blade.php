@if(isset($auctionCategoryItem['auctionProduct']) && count($auctionCategoryItem['auctionProduct']) > 0)
<div class="phone-telecom-section p-20 pe-xl-0 card border-0 shadow-sm rounded overflow-hidden position-relative mb-20">
    <div class="section-card-margin">
        <div class="border-bottom pb-3 d-flex justify-content-between align-items-center gap-2 mb-3 pe-xl-3">
            <h2 class="title-border-cus m-0 text-capitalize fw-bold fs-5 d-column align-items-center gap-1">
                <span class="line--limit-1 pragraph-clr2">
                    {{ $auctionCategoryItem['name'] }}
                </span>
            </h2>
            <div class="text-end">
                <a class="d-flex align-items-center text-nowrap gap-1 text-capitalize text-decoration-none fs-14 view-all-text title-clr"
                   href="{{ route('auction.category-products', ['slug' => $auctionCategoryItem?->slug]) }}">
                    {{ translate('view_all')}}
                    <i class="fi fi-rr-angle-right fs-12 text-primary"></i>
                </a>
            </div>
        </div>
        <div class="all-viewed-product position-relative">
            <div class="swiper-container">
                <div class="position-relative">
                    <div class="swiper" data-swiper-margin="16"
                         data-swiper-pagination-el="null" data-swiper-navigation-next=".product-nav-next-{{ $auctionCategoryItem?->id }}"
                         data-swiper-navigation-prev=".product-nav-prev-{{ $auctionCategoryItem?->id }}"
                         data-swiper-breakpoints='{"0": {"slidesPerView": "1"}, "470": {"slidesPerView": "1.5"}, "992": {"slidesPerView": "2"}, "1200": {"slidesPerView": "2"}, "1400": {"slidesPerView": "2"}}'>
                        <div class="swiper-wrapper">
                            @foreach($auctionCategoryItem['auctionProduct'] as $auctionProduct)
                                <div class="swiper-slide">
                                    @include('auction.web-views.partials._auction-product-horizontal-thin', ['auctionProduct' => $auctionProduct])
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="swiper-button-next product-nav-next-{{ $auctionCategoryItem?->id }}"></div>
                    <div class="swiper-button-prev product-nav-prev-{{ $auctionCategoryItem?->id }}"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
