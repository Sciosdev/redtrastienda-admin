@if(isset($auctionCategories) && count($auctionCategories) > 0)
<div class="categories-section overflow-hidden position-relative mb-20">
    <div class="section-card-margin">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <h2 class="m-0 text-capitalize fw-bold fs-5 d-flex align-items-center gap-1">
                <span class="line--limit-1">{{ translate('Categories') }}</span>
            </h2>
        </div>
        <div class="categories-boxes position-relative">
            <div class="swiper-container">
                <div class="position-relative px-lg-0 px-1">
                    <div class="swiper" data-swiper-margin="16" data-swiper-autoplay="false"
                        data-swiper-pagination-el="null" data-swiper-navigation-next=".categories-nav-next"
                        data-swiper-navigation-prev=".categories-nav-prev"
                        data-swiper-breakpoints='{"0": {"slidesPerView": "1"}, "425": {"slidesPerView": "2"}, "992": {"slidesPerView": "3"}, "1200": {"slidesPerView": "4"}, "1400": {"slidesPerView": "4.2"}}'>
                        <div class="swiper-wrapper">
                            @foreach($auctionCategories as $category)
                            <div class="swiper-slide pb-1">
                                <div class="categories-card  w-100 card-hover-shadow bg-white border-0 p-12px shadow-sm rounded-10 position-relative">
                                    <div class="d-flex align-items-center gap-10px">
                                        <div class="rounded overflow-hidden w-60px h-60px min-w-60px">
                                            <a href="{{ route('auction.category-products', ['slug' => $category->slug]) }}" class="m-thumbnail d-block">
                                                <img alt="{{ $category['name'] }}" class="w-100 h-100"
                                                     src="{{ getStorageImages(path: $category->icon_full_url, type: 'category') }}">
                                            </a>
                                        </div>
                                        <div class="">
                                            <h6 class="mb-2">
                                                <a href="{{ route('auction.category-products', ['slug' => $category->slug]) }}" class="text-decoration-none fs-16 text-capitalize fw-semibold title-clr line--limit-1">
                                                    {{ $category['name'] }}
                                                </a>
                                            </h6>
                                            <div class="d-flex justify-content-start gap-2 align-items-center">
                                                <span class="title-clr fs-14">
                                                    {{ $category?->auction_product_count ?? 0 }}
                                                </span>
                                                <span class="fs-14 title-semidark">
                                                    {{ $category?->auction_product_count > 1 ? translate('Auctions') : translate('Auction') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="swiper-button-next categories-nav-next"></div>
                    <div class="swiper-button-prev categories-nav-prev"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
