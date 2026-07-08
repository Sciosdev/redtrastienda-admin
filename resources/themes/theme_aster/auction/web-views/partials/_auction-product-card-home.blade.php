@php
    $cardIsOwner = auth('customer')->check() && $auctionProduct->owner_type === 'customer' && auth('customer')->id() == $auctionProduct->owner_id;
    $cardIsParticipant = auth('customer')->check() && $auctionProduct->myParticipation !== null;
    $cardHasBid = auth('customer')->check() && $auctionProduct->myBid !== null;
@endphp
<div class="ending-soon-card ending-soon-card-home card-icon-support bg-white shadow-sm overflow-hidden rounded position-relative h-100">
    @if($auctionProduct['auction_current_status'] == 'upcoming')
        <span class="badge bg-success rounded-0 position-absolute top-0 inline-start-0 px-10px py-5px fs-13 text-absolute-white z-1">
            {{ translate('Upcoming') }}
        </span>
    @elseif($auctionProduct['auction_current_status'] == 'live')
        <span class="badge bg-danger rounded-0 position-absolute top-0 inline-start-0 px-10px py-5px fs-13 text-absolute-white z-1">
            {{ translate('Live') }}
        </span>
    @endif

    <div class="m-thumbnail-wrap overflow-hidden text-center position-relative">
        <a href="{{ route('auction.product-details', ['slug' => $auctionProduct?->slug]) }}" class="m-thumbnail d-block">
            <img src="{{ getStorageImages(path: $auctionProduct->thumbnail_full_url, type: 'product') }}" alt="{{ $auctionProduct['name'] }}" class="w-100 h-207px object-fit-cover">
        </a>
        <div class="countdown-time_box overflow-hidden z-1 d-inline-flex bg-white shadow-sm flex-sm-nowrap flex-wrap gap-1 justify-content-between align-items-center py-1 px-2 lh-sm position-absolute">
            <span class="fs-12 title-semidark text-nowrap status-text" data-timeend-text="{{ translate('Closed') }} :">
                {{ $auctionProduct['auction_current_status'] == 'upcoming' ? translate('Starts At') : translate('Closes In') }} :
            </span>
            <div class="d-flex align-items-center text-nowrap gap-1">
                <div class="d-flex gap-2px countdown auction-countdown"
                     data-end="{{ $auctionProduct['auction_current_status'] == 'upcoming'
                        ? \Carbon\Carbon::parse($auctionProduct->start_time)->format('Y-m-d H:i:s')
                        : (\Carbon\Carbon::parse($auctionProduct->end_time)->isPast() ? \Carbon\Carbon::now()->format('Y-m-d H:i:s') : \Carbon\Carbon::parse($auctionProduct->end_time)->format('Y-m-d H:i:s')) }}">
                    <div class="time-box d-flex align-items-center gap-1px text-center fw-bold">
                        <span class="time hours fw-bold title-clr fs-14">00</span>
                        <div class="small title-clr fs-14">h</div>
                    </div>
                    <div class="time-box d-flex align-items-center gap-1px text-center fw-bold">
                        <span class="time minutes fw-bold title-clr fs-14">00</span>
                        <div class="small title-clr fs-14">m</div>
                    </div>
                    <div class="time-box d-flex align-items-center gap-1px text-center fw-bold">
                        <span class="time seconds fw-bold title-clr fs-14">00</span>
                        <div class="small title-clr fs-14">s</div>
                    </div>
                </div>
                <i class="fi fi-rr-time-oclock fs-12 text-danger"></i>
            </div>
        </div>
    </div>

    <div class="">
        <div class="bg-light d-flex align-items-start justify-content-between gap-1 py-2 px-10">
            <h6 class="">
                <a href="{{ route('auction.product-details', ['slug' => $auctionProduct?->slug]) }}" class="text-decoration-none fs-14 text-capitalize fw-semibold title-clr line-clamp-1">
                    {{ $auctionProduct['name'] }}
                </a>
            </h6>
            <button type="button"
                data-based-class="auction-home-saved-update-{{ $auctionProduct->id }}"
                class="btn p-0 fs-16 outline-0 border-0 text-primary auction-home-saved-update auction-home-saved-update-{{ $auctionProduct->id }}"
                data-auction-product-id="{{ $auctionProduct->id }}">
                <i class="fi {{ $auctionProduct?->mySavedProduct ? 'fi-sr-bookmark' : 'fi-rr-bookmark' }} text-primary"></i>
            </button>
        </div>
        <div class="py-10 bg-white px-10">
            <div class="d-flex justify-content-start gap-2 mb-2 align-items-center">
                @if($auctionProduct?->current_highest_bid_amount > 0)
                    <span class="fs-12 title-semidark">{{ translate('Highest Bid') }}</span>
                    <strong class="text-primary fs-16 fw-bold">
                        {{ webCurrencyConverter(amount: $auctionProduct->current_highest_bid_amount) }}
                    </strong>
                @else
                    <span class="fs-12 title-semidark">{{ translate('Start Price') }}</span>
                    <strong class="text-primary fs-16 fw-bold">
                        {{ webCurrencyConverter(amount: $auctionProduct->starting_price) }}
                    </strong>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div>
                    <div class="d-flex gap-xxl-20px gap-2 small text-muted">
                        <span class="d-flex align-items-center gap-1 title-clr fs-12">
                            <i class="fi fi-sr-eye d-flex text-BFBFBF"></i>
                            {{ formatCompactNumber($auctionProduct->total_views) }}
                        </span>
                        <span class="d-flex align-items-center gap-1 title-clr fs-12">
                            <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/bid.svg') }}" alt="" class="svg">
                            {{ formatCompactNumber($auctionProduct->total_bids) }}
                        </span>
                    </div>
                </div>
                <div>
                    @if(!$cardIsOwner)
                        @if($auctionProduct['auction_current_status'] == 'upcoming')
                            @if($cardIsParticipant)
                                <button class="btn bg-primary bg-opacity-10 text-primary rounded-pill btn-sm px-2 py-1 fw-bold fs-12 lh-sm">
                                    {{ translate('Participated') }}
                                </button>
                            @elseif(!auth('customer')->check())
                                <button class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                        data-bs-toggle="modal" data-bs-target="#loginModal">
                                    {{ translate('Participate') }}
                                </button>
                            @else
                                <button class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm auction-participate-btn"
                                        data-url="{{ route('auction.participate.check') }}"
                                        data-details-url="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}"
                                        data-auction-product-id="{{ $auctionProduct->id }}">
                                    {{ translate('Participate') }}
                                </button>
                            @endif
                        @elseif($auctionProduct['auction_current_status'] == 'live')
                            @if(!auth('customer')->check())
                                <button class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                        data-bs-toggle="modal" data-bs-target="#loginModal">
                                    <i class="fi fi-rr-auction"></i>
                                    {{ translate('Participate') }}
                                </button>
                            @elseif(!$cardIsParticipant)
                                <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                   href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}?open_participate=1">
                                    {{ translate('Participate') }}
                                </a>
                            @elseif(!$cardHasBid)
                                <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                   href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}?open_bid=1">
                                    <i class="fi fi-rr-auction d-flex"></i>
                                    {{ translate('Bid') }}
                                </a>
                            @else
                                <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                   href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}?open_bid=1">
                                    <i class="fi fi-rr-auction d-flex"></i>
                                    {{ translate('Raise Bid') }}
                                </a>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
