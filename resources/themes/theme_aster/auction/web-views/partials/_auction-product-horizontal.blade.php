@php
    $bidListContext = $bidListContext ?? false;
    $bidListTab = $bidListTab ?? null;
    $myAuctionStatus = $bidListContext ? ($auctionProduct->my_auction_status ?? null) : null;
    $isClaimTimeout = $bidListContext && !empty($auctionProduct->is_claim_expired_loser);
    $isParticipatedTab = $bidListContext && $bidListTab === 'participated';
    $isLeadingBidder = false;
    $isOutbidBidder = false;
    if ($bidListContext && !$isParticipatedTab && $auctionProduct['auction_current_status'] == 'live' && auth('customer')->check() && $auctionProduct->myBid !== null) {
        $isLeadingBidder = $auctionProduct->highestBid && (int) $auctionProduct->highestBid->user_id === (int) auth('customer')->id();
        $isOutbidBidder = !$isLeadingBidder;
    }
@endphp
@if(isset($auctionProduct) && $auctionProduct)
<div class="ending-soon-card card-icon-support bg-white w-100 shadow-sm rounded position-relative h-100">
    @if($bidListContext && $isLeadingBidder)
        <span class="badge bg-success rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1"
              data-bs-toggle="tooltip" data-bs-placement="top"
              data-bs-title="{{ translate('You have placed the highest bid and are currently leading.') }}">
            {{ translate('Leading') }}
        </span>
    @elseif($bidListContext && $isOutbidBidder)
        <span class="badge bg-danger rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1"
              data-bs-toggle="tooltip" data-bs-placement="top"
              data-bs-title="{{ translate('Someone placed a higher bid. Raise your bid to regain the leading position.') }}">
            {{ translate('Outbid') }}
        </span>
    @elseif($auctionProduct['auction_current_status'] == 'upcoming')
        <span class="badge bg-success rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1">
            {{ translate('Upcoming') }}
        </span>
    @elseif($auctionProduct['auction_current_status'] == 'live')
        <span class="badge bg-danger rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1">
            {{ translate('live') }}
        </span>
    @endif

    <div class="m-thumbnail-wrap overflow-hidden text-center position-relative">
        <a href="{{ route('auction.product-details', ['slug' => $auctionProduct?->slug]) }}"
           class="m-thumbnail d-block">
            <img src="{{ getStorageImages(path: $auctionProduct->thumbnail_full_url, type: 'product') }}"
                 alt="" class="w-100 h-207px object-fit-cover">
        </a>
        <div class="icons-grop position-absolute d-grid align-items-center gap-xl-10px">
            <button type="button"
                    class="d-flex icon-primary align-items-center justify-content-center bg-white rounded-circle shadow-sm icon">
                <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/bid.svg') }}" alt=""
                     class="svg">
            </button>
            <button type="button"
                    class="d-flex icon-primary align-items-center justify-content-center bg-white rounded-circle shadow-sm icon">
                <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/card-badge.svg') }}"
                     alt="" class="svg">
            </button>
        </div>

        <div class="countdown-time_box z-1 d-inline-flex bg-white shadow-sm flex-sm-nowrap flex-wrap gap-1 justify-content-between align-items-center py-5px px-2 lh-sm position-absolute">
            @if($isClaimTimeout)
                <span class="fs-12 fw-semibold text-danger text-capitalize">{{ translate('Time Out') }}</span>
            @else
                <span class="fs-12 title-semidark text-nowrap status-text"
                      data-timeend-text="{{ translate('Closed') }} :">
                    {{ $auctionProduct['auction_current_status'] == 'upcoming' ? translate('Starts At') : translate('Closes In') }} :
                </span>
                <div class="flex-aligns text-nowrap gap-1">
                    <div class="d-flex gap-2px countdown auction-countdown"
                         data-end="{{ $auctionProduct['auction_current_status'] == 'upcoming'
                            ? \Carbon\Carbon::parse($auctionProduct->start_time)->format('Y-m-d H:i:s')
                            : (\Carbon\Carbon::parse($auctionProduct->end_time)->isPast() ? \Carbon\Carbon::now()->format('Y-m-d H:i:s') : \Carbon\Carbon::parse($auctionProduct->end_time)->format('Y-m-d H:i:s')) }}">
                        <div class="time-box flex-aligns gap-1px text-center fw-bold">
                            <span class="time hours fw-bold title-clr fs-14">00</span>
                            <div class="small title-clr fs-14">h</div>
                        </div>
                        <div class="time-box flex-aligns gap-1px text-center fw-bold">
                            <span class="time minutes fw-bold title-clr fs-14">00</span>
                            <div class="small title-clr fs-14">m</div>
                        </div>
                        <div class="time-box flex-aligns gap-1px text-center fw-bold">
                            <span class="time seconds fw-bold title-clr fs-14">00</span>
                            <div class="small title-clr fs-14">s</div>
                        </div>
                    </div>
                    <i class="fi fi-rr-time-oclock fs-12 text-danger"></i>
                </div>
            @endif
        </div>
    </div>
    <div class="">
        <div class="light-box d-flex align-items-start justify-content-between gap-1 py-2 px-10">
            <h6 class="">
                <a href="{{ route('auction.product-details', ['slug' => $auctionProduct?->slug]) }}"
                   class="text-decoration-none fs-14 text-capitalize fw-semibold title-clr line--limit-2">
                    {{ $auctionProduct['name'] }}
                </a>
            </h6>
            <button type="button" class="btn p-0 fs-16 outline-0 border-0 text-primary">
                <i class="fi fi-rr-bookmark"></i>
            </button>
        </div>
        <div class="py-10 bg-white px-10">
            <div class="d-flex flex-wrap justify-content-start gap-5px mb-12px align-items-center">
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
                            <i class="fi fi-sr-eye text-light-gray"></i>
                            {{ formatCompactNumber($auctionProduct->total_views) }}
                        </span>
                        <span class="d-flex align-items-center gap-1 title-clr fs-12">
                            <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/bid.svg') }}"
                                 alt="" class="svg">
                            {{ formatCompactNumber($auctionProduct->total_bids) }}
                        </span>
                    </div>
                </div>
                <div>
                    @php
                        $cardIsOwner = auth('customer')->check() && $auctionProduct->owner_type === 'customer' && auth('customer')->id() == $auctionProduct->owner_id;
                        $cardIsParticipant = auth('customer')->check() && $auctionProduct->myParticipation !== null;
                        $cardHasBid = auth('customer')->check() && $auctionProduct->myBid !== null;
                    @endphp
                    @if($isParticipatedTab && $auctionProduct['auction_current_status'] == 'upcoming')
                        <div data-bs-toggle="tooltip" data-bs-placement="top"
                             data-bs-title="{{ translate('Kindly wait for the auction to go live to start bidding.') }}">
                            <button type="button" class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm" disabled>
                                <i class="fi fi-rr-auction"></i>
                                {{ translate('Bid') }}
                            </button>
                        </div>
                    @elseif($isParticipatedTab && $auctionProduct['auction_current_status'] == 'live')
                        <button type="button" class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm">
                            <i class="fi fi-rr-auction"></i>
                            {{ translate('Bid') }}
                        </button>
                    @elseif($bidListContext && $myAuctionStatus === 'won' && !$isClaimTimeout)
                        <a href="{{ auth('customer')->check() && auth('customer')->id() == $auctionProduct?->owner_id && $auctionProduct?->owner_type == 'customer' ? route('auction.profile-view.author-product-details', ['slug' => $auctionProduct?->slug]) : route('auction.profile-view.product-details', ['slug' => $auctionProduct?->slug]) }}"
                           class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm">
                            {{ translate('Claim Product') }}
                        </a>
                    @elseif($bidListContext && $myAuctionStatus === 'claimed')
                        <a href="{{ route('track-order.index') }}"
                           class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm">
                            {{ translate('Track Order') }}
                        </a>
                    @elseif(!$cardIsOwner)
                        @if($auctionProduct['auction_current_status'] == 'upcoming')
                            @if($cardIsParticipant)
                                <button class="btn bg-primary bg-opacity-10 text-primary rounded-pill btn-sm px-2 py-1 fw-bold fs-12 lh-sm">
                                    {{ translate('Participated') }}
                                </button>
                            @elseif(!auth('customer')->check())
                                <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                   href="{{ route('customer.auth.login') }}">
                                    {{ translate('Participate') }}
                                </a>
                            @else
                                <button class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm">
                                    {{ translate('Participate') }}
                                </button>
                            @endif
                        @elseif($auctionProduct['auction_current_status'] == 'live')
                            @if(!auth('customer')->check())
                                <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                   href="{{ route('customer.auth.login') }}">
                                    <i class="fi fi-rr-auction"></i>
                                    {{ translate('Bid') }}
                                </a>
                            @elseif(!$cardIsParticipant)
                                <button class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm">
                                    {{ translate('Participate') }}
                                </button>
                            @elseif(!$cardHasBid)
                                <button class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm">
                                    <i class="fi fi-rr-auction"></i>
                                    {{ translate('Bid') }}
                                </button>
                            @else
                                <button class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm">
                                    <i class="fi fi-rr-auction"></i>
                                    {{ translate('Raise Bid') }}
                                </button>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(isset($actionBtn) && $actionBtn == true)
        <div class="position-absolute inline-end-0 top-0 m-xxl-2 m-xl-2 m-1">
            <div class="btn-group dropstart">
                <button type="button" class="btn p-0 bg-transparent outline-0 border-0 shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fi fi-sr-menu-dots-vertical text-primary"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu2 shadow-sm px-2">
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1"
                           href="{{ auth('customer')->check() && auth('customer')->id() == $auctionProduct?->owner_id && $auctionProduct?->owner_type == 'customer' ? route('auction.profile-view.author-product-details', ['slug' => $auctionProduct?->slug]) : route('auction.profile-view.product-details', ['slug' => $auctionProduct?->slug]) }}">
                            {{ translate('View Details') }} <i class="fi fi-sr-eye text-primary"></i>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1" href="#">
                            {{ translate('Raise Bid') }} <i class="fi fi-sr-auction text-primary"></i>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1" href="#">
                            {{ translate('Withdraw Bid') }}
                            <img width="16" height="16" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/logout-up.svg') }}" alt="">
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    @endif
</div>
@endif
