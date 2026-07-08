@extends("auction.web-views.user-profile._profile-master")

@section('title', $auctionProduct['name'])

@section('profile_content')
    <span id="all-msg-container" data-afterextend="{{ translate('See Less') }}" data-seemore="{{ translate('See More') }}"></span>

    @if($isAuctionInactiveState)
        @include('auction.web-views.user-profile.partials.product-details._owner-inactive-state')
    @else
        <div>
            @if($isPurchaseComplete || $isMyAuctionClaimed)
                @include('auction.web-views.user-profile.partials.product-details._claimed-header')
            @endif

            <div class="bid-product-details__wrapper d-flex gap-3">
                @if(!$isPurchaseComplete && !$isMyAuctionClaimed)
                    @include('auction.web-views.user-profile.partials.product-details._main-card-default')
                @else
                    @include('auction.web-views.user-profile.partials.product-details._main-card-claimed')
                @endif
    
                <div class="bids-product-details-right">
                    @if(!$isPurchaseComplete)
                        @if($isMyAuctionClaimed)
                            @include('auction.web-views.user-profile.partials.product-details._sidebar-claimed-for-author')
                        @endif

                        @if(!$isMyAuctionClaimed)
                            @include('auction.web-views.user-profile.partials.product-details._sidebar-default')
                        @endif
                    @endif

                    @if($isPurchaseComplete)
                        @include('auction.web-views.user-profile.partials.product-details._sidebar-purchase-complete')
                    @endif
                </div>
            </div>
        </div>
    @endif

    @include('auction.web-views.user-profile.partials.product-details._modals')
@endsection

@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/libs/plyr/plyr.css') }}"/>
@endpush

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/backend/libs/plyr/plyr.js') }}"></script>
    @include('auction.web-views.user-profile.partials.product-details._scripts')
@endpush
