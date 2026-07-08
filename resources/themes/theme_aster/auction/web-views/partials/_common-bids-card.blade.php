@php
    $bidListContext    = $bidListContext ?? false;
    $bidListTab        = $bidListTab    ?? null;
    $myAuctionStatus   = $bidListContext ? ($auctionProduct->my_auction_status ?? null) : null;
    $isClaimTimeout    = $bidListContext && !empty($auctionProduct->is_claim_expired_loser);
    $isParticipatedTab = $bidListContext && $bidListTab === 'participated';
    $isLeadingBidder   = false;
    $isOutbidBidder    = false;
    if ($bidListContext && $auctionProduct['auction_current_status'] == 'live' && auth('customer')->check() && $auctionProduct->myBid !== null) {
        $isLeadingBidder = $auctionProduct->highestBid && (int) $auctionProduct->highestBid->user_id === (int) auth('customer')->id();
        $isOutbidBidder  = !$isLeadingBidder;
    }
    $cardIsOwner      = auth('customer')->check() && $auctionProduct->owner_type === 'customer' && auth('customer')->id() == $auctionProduct->owner_id;
    $cardIsParticipant = auth('customer')->check() && $auctionProduct->myParticipation !== null;
    $cardHasBid       = auth('customer')->check() && $auctionProduct->myBid !== null;
    $cardProfileViewUrl = $cardIsOwner
        ? route('auction.profile-view.author-product-details', ['slug' => $auctionProduct?->slug])
        : route('auction.profile-view.product-details', ['slug' => $auctionProduct?->slug]);
    $cardClickUrl = ($bidListContext && ($myAuctionStatus === 'lost' || $isClaimTimeout))
        ? $cardProfileViewUrl
        : route('auction.product-details', ['slug' => $auctionProduct?->slug]);
@endphp
<div class="ending-soon-card card-icon-support time_box__responsive bg-white shadow-sm rounded position-relative h-100">
    @if($bidListContext && $isLeadingBidder)
        <span class="badge bg-success rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1"
              data-bs-toggle="tooltip" data-bs-placement="top"
              data-bs-title="{{ translate('As you have placed the highest bid, you\'re in leading position') }}">
            {{ translate('Leading') }}
        </span>
    @elseif($bidListContext && $isOutbidBidder)
        <span class="badge bg-danger rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1"
              data-bs-toggle="tooltip" data-bs-placement="top"
              data-bs-title="{{ translate('Someone placed a higher bid. Raise your bid to regain the leading position.') }}">
            {{ translate('Outbid') }}
        </span>
    @elseif($auctionProduct['auction_current_status'] == 'live')
        <span class="badge bg-danger rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1">
            {{ translate('Live') }}
        </span>
    @elseif($auctionProduct['auction_current_status'] == 'upcoming')
        <span class="badge bg-success rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1">
            {{ translate('Upcoming') }}
        </span>
    @elseif($auctionProduct['auction_current_status'] == 'unsold')
        <span class="badge bg-danger rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1">
            {{ translate('Expired') }}
        </span>
    @endif

    <div class="d-flex">
        <div class="m-thumbnail-wrap minmax-160px overflow-hidden text-center position-relative">
            <a href="{{ $cardClickUrl }}" class="m-thumbnail d-block">
                <img src="{{ getStorageImages(path: $auctionProduct->thumbnail_full_url, type: 'product') }}" alt="" class="w-100 object-fit-cover">
            </a>
            @unless(in_array($myAuctionStatus, ['won', 'lost', 'claim_expired_lost'], true))
            @if($isClaimTimeout || ($bidListContext && $myAuctionStatus === 'claimed') || ($auctionProduct['auction_current_status'] == 'upcoming' || $auctionProduct['auction_current_status'] == 'live'))
                <div class="countdown-time_box bottom-0 w-100 rounded-0 z-1 d-inline-flex bg-white shadow-sm flex-wrap gap-1 justify-content-between align-items-center py-5px px-2 lh-sm position-absolute">
                    @if($isClaimTimeout)
                        <span class="fs-12 fw-semibold text-danger text-capitalize w-100 text-center">{{ translate('Time Out') }}</span>
                    @elseif($bidListContext && $myAuctionStatus === 'claimed')
                        @php
                            $deliveryLabel = match($auctionProduct->delivery_status) {
                                'ready_to_delivery' => translate('Ready to Delivery'),
                                'on_the_way'        => translate('On the Way'),
                                'delivered'         => translate('Delivered'),
                                default             => translate('Processing'),
                            };
                            $deliveryClass = match($auctionProduct->delivery_status) {
                                'delivered'  => 'text-success',
                                'on_the_way' => 'text-primary',
                                default      => 'text-warning',
                            };
                        @endphp
                        <span class="fs-12 fw-semibold text-capitalize w-100 text-center {{ $deliveryClass }}">{{ $deliveryLabel }}</span>
                    @elseif($auctionProduct['auction_current_status'] == 'upcoming' || $auctionProduct['auction_current_status'] == 'live')
                        <span class="fs-12 status-text title-semidark text-nowrap">
                            {{ $auctionProduct['auction_current_status'] == 'upcoming' ? translate('Start At') : translate('Closes In') }} :
                        </span>
                        <div class="flex-aligns text-nowrap gap-1">
                            @if($auctionProduct['auction_current_status'] != 'unsold')
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
                            @endif
                        </div>
                    @endif
                </div>
                @endif
            @endunless
        </div>
        <div class="flex-row justify-content-between py-10 px-10px w-100 d-flex flex-column">
            <div>
                <div class="fs-12 title-semidark mb-2">{{ translate('Auction ID') }}#{{ $auctionProduct->id }}</div>
                <div class="d-flex align-items-start justify-content-between gap-1 mb-2">
                    <h6 class="me-xl-3 me-1">
                        <a href="{{ $cardClickUrl }}"
                           class="text-decoration-none fs-13 text-capitalize fw-semibold title-clr line--limit-2">
                            {{ $auctionProduct['name'] }}
                        </a>
                    </h6>
                </div>
                <div class="d-flex justify-content-start gap-5px mb-2 align-items-center flex-wrap">
                    @if(($myAuctionStatus ?? null) === 'claimed' && $auctionProduct->claimTransaction)
                        <span class="fs-12 title-semidark">{{ translate('Claimed_At') }}</span>
                        <strong class="title-clr fs-12 fw-medium">
                            {{ $auctionProduct->claimTransaction->created_at?->format('d M Y, h:i A') }}
                        </strong>
                    @else
                        <span class="fs-12 title-semidark">{{ translate('Start Price') }}</span>
                        <strong class="title-clr fs-16 fw-medium">{{ webCurrencyConverter(amount: $auctionProduct->starting_price) }}</strong>
                    @endif
                </div>
                @if($auctionProduct->current_highest_bid_amount > 0)
                    <div class="d-flex justify-content-start gap-5px mb-2 align-items-center flex-wrap">
                        <span class="fs-12 title-semidark">
                            {{ translate('Highest Bid') }}
                        </span>
                        <strong class="text-primary fs-16 fw-bold">
                            {{ webCurrencyConverter(amount: $auctionProduct->current_highest_bid_amount) }}
                        </strong>
                    </div>
                @endif
            </div>
            <div>
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <div>
                        <div class="d-flex gap-xxl-20px gap-2 small text-muted">
                            <span class="d-flex align-items-center gap-1 title-clr fs-12">
                                <i class="fi fi-sr-eye text-light-gray"></i>
                                {{ formatCompactNumber($auctionProduct->total_views) }}
                            </span>
                            <span class="d-flex align-items-center gap-1 title-clr fs-12">
                                <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/bid.svg') }}" alt="" class="svg">
                                {{ formatCompactNumber($auctionProduct->total_bids) }}
                            </span>
                        </div>
                    </div>
                    <div>
                        @if($isParticipatedTab && $auctionProduct['auction_current_status'] == 'upcoming')
                            <div data-bs-toggle="tooltip" data-bs-placement="right"
                                 data-bs-title="{{ translate('Kindly wait for the auction to go live to start bidding.') }}">
                                <button disabled class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm">
                                    <i class="fi fi-rr-auction"></i>
                                    {{ translate('Bid') }}
                                </button>
                            </div>
                        @elseif($isParticipatedTab && $auctionProduct['auction_current_status'] == 'live')
                            <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                               href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}?open_bid=1">
                                <i class="fi fi-rr-auction"></i>
                                {{ $cardHasBid ? translate('Raise Bid') : translate('Bid') }}
                            </a>
                        @elseif($bidListContext && $myAuctionStatus === 'won' && !$isClaimTimeout)
                            <a href="{{ route('auction.claim.checkout', ['auctionProductId' => $auctionProduct->id]) }}"
                               class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm">
                                {{ translate('Claim Product') }}
                            </a>
                        @elseif($bidListContext && $myAuctionStatus === 'claimed')
                            @if(!empty($auctionProduct->tracking_url))
                                <a href="{{ $auctionProduct->tracking_url }}" target="_blank" rel="noopener noreferrer"
                                   class="btn btn--secondary text--primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm">
                                    {{ translate('Track Order') }}
                                </a>
                            @else
                                <span class="d-inline-flex" tabindex="0" data-bs-toggle="tooltip" data-bs-title="{{ translate('Tracking URL not provided yet') }}">
                                    <button type="button" disabled class="btn btn--secondary text--primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm" style="pointer-events:none;">
                                        {{ translate('Track Order') }}
                                    </button>
                                </span>
                            @endif
                        @elseif(!$cardIsOwner)
                            @if($auctionProduct['auction_current_status'] == 'upcoming')
                                @if($cardIsParticipant)
                                    <a class="btn bg-primary bg-opacity-10 text-primary rounded-pill btn-sm px-2 py-1 fw-bold fs-12 lh-sm"
                                       href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}">
                                        {{ translate('Participated') }}
                                    </a>
                                @elseif(!auth('customer')->check())
                                    <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                       href="{{ route('customer.auth.login') }}">
                                        {{ translate('Participate') }}
                                    </a>
                                @else
                                    <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                       href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}">
                                        {{ translate('Participate') }}
                                    </a>
                                @endif
                            @elseif($auctionProduct['auction_current_status'] == 'live')
                                @if(!auth('customer')->check())
                                    <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                       href="{{ route('customer.auth.login') }}">
                                        <i class="fi fi-rr-auction"></i>
                                        {{ translate('Bid') }}
                                    </a>
                                @elseif(!$cardIsParticipant)
                                    <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                       href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}">
                                        {{ translate('Participate') }}
                                    </a>
                                @elseif(!$cardHasBid)
                                    <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                       href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}?open_bid=1">
                                        <i class="fi fi-rr-auction"></i>
                                        {{ translate('Bid') }}
                                    </a>
                                @else
                                    <a class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold d-flex align-items-center gap-1 fs-12 lh-sm"
                                       href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}?open_bid=1">
                                        <i class="fi fi-rr-auction"></i>
                                        {{ translate('Raise Bid') }}
                                    </a>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @if(isset($actionBtn) && $actionBtn == true)
            @php
                $viewOnlyStatuses = ['won', 'claimed', 'lost'];
                $showBidActions   = !in_array($bidListTab, $viewOnlyStatuses)
                    && $auctionProduct['auction_current_status'] === 'live'
                    && $auctionProduct?->myBid;
            @endphp
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
                        @if($myAuctionStatus === 'claimed')
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1"
                               href="{{ route('auction.generate-invoice', $auctionProduct->id) }}">
                                {{ translate('Invoice') }} <i class="fi fi-sr-file-invoice text-primary"></i>
                            </a>
                        </li>
                        @endif
                        @if($auctionProduct['auction_current_status'] === 'live' && !$cardIsOwner && !$cardHasBid)
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1"
                                   href="{{ route('auction.profile-view.product-details', ['slug' => $auctionProduct->slug]) }}?open_bid=1">
                                    {{ translate('Bid') }} <i class="fi fi-sr-auction text-primary"></i>
                                </a>
                            </li>
                        @endif
                        @if($showBidActions)
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1"
                                   href="{{ auth('customer')->check() && auth('customer')->id() == $auctionProduct?->owner_id && $auctionProduct?->owner_type == 'customer' ? route('auction.profile-view.author-product-details', ['slug' => $auctionProduct->slug]) : route('auction.profile-view.product-details', ['slug' => $auctionProduct->slug]) }}?open_bid=1">
                                    {{ translate('Raise Bid') }} <i class="fi fi-sr-auction text-primary"></i>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1 js-withdraw-bid-btn"
                                   href="#"
                                   data-auction-product-id="{{ $auctionProduct->id }}"
                                   data-withdraw-url="{{ route('auction.bids.withdraw') }}">
                                    {{ translate('Withdraw Bid') }}
                                    <img width="16" height="16" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/logout-up.svg') }}" alt="">
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>
