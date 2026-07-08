@once
@push('script')
<script>
    // Save / unsave an auction product from the home slider card
    $(document).on('click', '.auction-home-saved-update', function () {
        let $btn = $(this);
        let auctionProductId = $btn.data('auction-product-id');
        let basedClass = $btn.data('based-class');

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') } });

        $.ajax({
            url: '{{ route("auction.saved-products.toggle") }}',
            method: 'POST',
            data: { auction_product_id: auctionProductId },
            success: function (data) {
                $('.' + basedClass).each(function () {
                    let $icon = $(this).find('i.fi');
                    if ($icon.length) {
                        $icon.toggleClass('fi-rr-bookmark', !data?.is_saved)
                             .toggleClass('fi-sr-bookmark', !!data?.is_saved);
                    }
                });
                if (data?.message) toastr.success(data.message);
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('loginModal')).show();
                }
            },
        });
    });

    // Participation from the home slider card is handled by the shared
    // `.auction-participate-btn` delegate in auction-common.js (redirect to details + toast).
</script>
@endpush
@endonce

@if(isset($auctionProducts) && count($auctionProducts) > 0)
<div class="container">
    <div class="auction-products-wrap">
        <div class="auction-product-svg-bg" style="background-image: url({{ dynamicAsset(path: "public/assets/front-end/auction/images/auction-bg.svg") }})"></div>
        <div class="d-flex justify-content-between align-items-baseline mb-3">
            <h2 class="d-flex align-items-center text-uppercase gap-2 font-bold m-0 text-capitalize">
                <img width="18" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/sale-icon1.png') }}" alt="img">
                {{ translate('Auction Products') }}
            </h2>
            <div class="text-end d-none d-md-block">
                <a class="text-capitalize view-all-text d-flex align-items-center gap-1 bg-white fw-semibold shadow-sm rounded py-2 px-3 btn-link" href="{{ route('auction.index') }}">
                    {{ translate('Explore Auction')}}
                    <i class="fi fi-rr-arrow-small-right d-flex fs-18"></i>
                </a>
            </div>
        </div>
        <div class="ending-soon-product position-relative">
            <div class="swiper-container">
                <div class="position-relative">
                    <div class="swiper" data-swiper-margin="16"
                        data-swiper-pagination-el="null" data-swiper-navigation-next=".ending-nav-next"
                        data-swiper-navigation-prev=".ending-nav-prev"
                        data-swiper-breakpoints='{"0": {"slidesPerView": "1"}, "425": {"slidesPerView": "2"}, "992": {"slidesPerView": "3"}, "1200": {"slidesPerView": "4"}, "1400": {"slidesPerView": "4.2"}}'>
                        <div class="swiper-wrapper">
                            @foreach($auctionProducts as $auctionProduct)
                                <div class="swiper-slide">
                                    @include('auction.web-views.partials._auction-product-card-home', ['auctionProduct' => $auctionProduct])
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
