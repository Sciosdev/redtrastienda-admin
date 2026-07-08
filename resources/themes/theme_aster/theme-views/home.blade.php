@extends('theme-views.layouts.app')

@section('title', $web_config['meta_title'])

@push('css_or_js')
    <script>
        (function () {
            try {
                var hu = parseInt(localStorage.getItem('auction_badge_hidden_until') || '0', 10);
                if (hu && Date.now() < hu) {
                    var s = document.createElement('style');
                    s.textContent = '.auction__badge{display:none !important}';
                    document.head.appendChild(s);
                } else if (hu) {
                    localStorage.removeItem('auction_badge_hidden_until');
                }
            } catch (e) {}
        })();
    </script>
@endpush

@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3">
        <?php
        $orderSuccessIds = session('order_success_ids');
        $isNewCustomerInSession = session('isNewCustomerInSession');
        session()->forget('order_success_ids');
        session()->forget('isNewCustomerInSession');
        ?>
        @include("theme-views.partials._order-success-modal",['orderSuccessIds' => $orderSuccessIds, 'isNewCustomerInSession' => $isNewCustomerInSession])

        @include('theme-views.partials._main-banner')

        @if ($flashDeal['flashDeal'] && $flashDeal['flashDealProducts']  && count($flashDeal['flashDealProducts']) > 0)
            @include('theme-views.partials._flash-deals')
        @endif

        @include('theme-views.partials._find-what-you-need')

        @include('theme-views.partials._clearance-sale', ['clearanceSaleProducts' => $clearanceSaleProducts])

        @if(getWebConfig(name: 'auction_feature_status'))
            @include('auction.web-views.partials._auction-home-slider', ['auctionProducts' => $auctionProducts])
        @endif

        @if ($web_config['business_mode'] == 'multi' && count($topVendorsList) > 0 && $topVendorsListSectionShowingStatus)
            @include('theme-views.partials._top-stores')
        @endif

        @if (getFeaturedDealsProductList()->count() > 0)
            @include('theme-views.partials._featured-deals')
        @endif

        @include('theme-views.partials._recommended-product')
        @if($web_config['business_mode'] == 'multi')
            @include('theme-views.partials._more-stores')
        @endif

        @include('theme-views.partials._top-rated-products')

        @include('theme-views.partials._best-deal-just-for-you')

        @include('theme-views.partials._home-categories')

        @if (!empty($bannerTypeMainSectionBanner))
        <section class="">
            <div class="container">
                <div class="py-5 rounded position-relative">
                    <img src="{{ getStorageImages(path: $bannerTypeMainSectionBanner->photo_full_url??null, type:'banner') }}"
                         alt="{{ translate('Banner') }}" class="rounded position-absolute dark-support img-fit start-0 top-0 index-n1 flipX-in-rtl">
                    <div class="row justify-content-center">
                        <div class="col-10 py-4">
                            <h6 class="text-primary mb-2 text-capitalize">{{ translate('do_not_miss_today`s_deal') }}!</h6>
                            <h2 class="fs-2 mb-4 absolute-dark text-capitalize">{{ translate('let_us_shopping_today') }}</h2>
                            <div class="d-flex">
                                <a href="{{ $bannerTypeMainSectionBanner ? $bannerTypeMainSectionBanner->url : '' }}"
                                   class="btn btn-primary fs-16 text-capitalize">
                                    {{ translate('shop_now') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @if(function_exists('getCheckAddonPublishedStatus') && getCheckAddonPublishedStatus(moduleName: 'Auction') && getWebConfig(name: 'auction_feature_status'))
        <div class="auction__badge dropdown">
            <a href="javascript:" class="btn_auction p-0 d-flex align-items-center justify-content-center" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/sale-icon1.png') }}"
                     width="31" alt="img" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ translate('See Auction') }}">
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a href="{{ route('auction.index') }}" class="dropdown-item d-flex align-items-center gap-2 fs-14 text-black">
                        <i class="fi fi-sr-compass-alt text-primary"></i>
                        {{ translate('Explore Auction') }}
                        <i class="fi fi-sr-arrow-small-right fs-16 text-black"></i>
                    </a>
                </li>
                @if(getWebConfig(name: 'active_auction_for_customer'))
                    <li>
                        @if(auth('customer')->check())
                            <a href="{{ route('user-profile') }}" class="dropdown-item d-flex align-items-center gap-2 fs-14 text-black">
                                <i class="fi fi-sr-add text-primary"></i>
                                {{ translate('Create Auction') }}
                                <i class="fi fi-sr-arrow-small-right fs-16 text-black"></i>
                            </a>
                        @else
                            <a href="javascript:" class="dropdown-item d-flex align-items-center gap-2 fs-14 text-black"
                               data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fi fi-sr-add text-primary"></i>
                                {{ translate('Create Auction') }}
                                <i class="fi fi-sr-arrow-small-right fs-16 text-black"></i>
                            </a>
                        @endif
                    </li>
                @endif
            </ul>
            <button type="button" class="auction_remove d-center btn p-0 outline-0 border-0">
                <i class="fi fi-sr-cross d-flex"></i>
            </button>
        </div>
        @endif

    </main>
@endsection

@push('script')
    @if($orderSuccessIds)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalEl = document.getElementById('order_successfully');
                const orderModal = new bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
                orderModal.show();

                document.querySelectorAll('.copy-order-id').forEach(function(copyBtn) {
                    copyBtn.addEventListener('click', function() {
                        let orderTextEl = null;
                        orderTextEl = this.closest('tr')?.querySelector('.order-id-text');
                        if (!orderTextEl) {
                            orderTextEl = this.parentElement.querySelector('.order-id-text');
                        }
                        const orderText = orderTextEl?.textContent.trim();
                        if (orderText) {
                            navigator.clipboard.writeText(orderText).then(() => {
                                toastr.success('Order ID copied successfully!');
                            }).catch(err => {
                                console.warn('Clipboard error:', err);
                                toastr.warning('Unable to copy. Clipboard requires HTTPS or localhost.');
                            });
                        }
                    });
                });
                const closeBtn = document.getElementById('modal-close-btn');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        setTimeout(() => { orderModal.hide(); }, 600);
                    });
                }
            });
        </script>
    @endif

    @if(Request::is('/') && Cookie::has('popup_banner') === false && empty($orderSuccessIds))
        <script>
            $(document).ready(function () {
                $('#initialModal').modal('show');
            });
        </script>
        @php(Cookie::queue('popup_banner', 'off', 1))
    @endif
@endpush


