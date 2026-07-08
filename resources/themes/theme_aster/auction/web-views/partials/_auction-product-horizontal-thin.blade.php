@php
    $cardIsOwner = auth('customer')->check() && $auctionProduct->owner_type === 'customer' && auth('customer')->id() == $auctionProduct->owner_id;
    $cardIsParticipant = auth('customer')->check() && $auctionProduct->myParticipation !== null;
    $cardHasBid = auth('customer')->check() && $auctionProduct->myBid !== null;
@endphp
<div class="ending-soon-card time_box__responsive card-icon-support bg-white w-100 shadow-sm rounded position-relative h-100">

    @if($auctionProduct['auction_current_status'] == 'upcoming')
        <span class="badge bg-success rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1">
            {{ translate('Upcoming') }}
        </span>
    @elseif($auctionProduct['auction_current_status'] == 'live')
        <span class="badge bg-danger rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1">
            {{ translate('Live') }}
        </span>
    @elseif($auctionProduct['auction_current_status'] == 'unsold')
        <span class="badge bg-danger rounded-4px position-absolute top-0 inline-start-0 px-10px py-5px fs-13 absolute-text-white z-1">
            {{ translate('Expired') }}
        </span>
    @endif

    <div class="d-flex">
        <div class="m-thumbnail-wrap minmax-160px overflow-hidden text-center position-relative">

            <a href="{{ route('auction.product-details', ['slug' => $auctionProduct?->slug]) }}" class="m-thumbnail d-block">
                <img src="{{ getStorageImages(path: $auctionProduct->thumbnail_full_url, type: 'product') }}"
                     alt="{{ $auctionProduct['name'] }}" class="w-100 object-fit-cover">
            </a>

            @if (in_array($auctionProduct['auction_current_status'], ['upcoming', 'live']))
            <div class="countdown-time_box bottom-0 w-100 rounded-0 z-1 d-inline-flex bg-white shadow-sm flex-wrap gap-1 justify-content-between align-items-center py-5px px-2 lh-sm position-absolute">
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
            </div>
            @endif
        </div>

        <div class="flex-row justify-content-between py-10 px-10px w-100 d-flex flex-column">
            <div>
                @if(isset($pageType) && $pageType == 'saved-page')
                    <div class="fs-12 title-semidark mb-2">
                        {{ translate('Auction ID') }} #{{ $auctionProduct?->id }}
                    </div>
                @endif

                <div class="d-flex align-items-start justify-content-between gap-1 mb-12px">
                    <h6 class="">
                        <a href="{{ route('auction.product-details', ['slug' => $auctionProduct?->slug]) }}"
                           class="text-decoration-none fs-14 text-capitalize fw-semibold title-clr line--limit-2    ">
                            {{ $auctionProduct['name'] }}
                        </a>
                    </h6>

                    @if(!isset($pageType) || $pageType != 'saved-page')
                        @if(auth('customer')->check())
                            <button type="button"
                                    data-based-class="auction-saved-update-{{ $auctionProduct?->id }}"
                                    class="btn p-0 fs-16 outline-0 border-0 text-primary auction-saved-update auction-saved-update-{{ $auctionProduct?->id }} {{ $auctionProduct?->mySavedProduct ? 'is-saved' : '' }}"
                                    data-auction-product-id="{{ $auctionProduct->id }}">
                                <i class="fi fi-rr-bookmark text-primary icon-unsaved"></i>
                                <i class="fi fi-sr-bookmark text-primary icon-saved"></i>
                            </button>
                        @else
                            <button type="button"
                                    class="btn p-0 fs-16 outline-0 border-0 text-primary"
                                    data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fi fi-rr-bookmark text-primary"></i>
                            </button>
                        @endif
                    @endif
                </div>

                <div class="d-flex justify-content-start gap-5px mb-12px align-items-center">
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
            </div>

            <div>
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <div>
                        <div class="d-flex gap-xxl-20px gap-2 small text-muted">
                            <span class="d-flex align-items-center gap-1 title-clr fs-12">
                                <i class="fi fi-sr-eye text-light-gray"></i>
                                {{ formatCompactNumber($auctionProduct->total_views) }}
                            </span>
                            @if($auctionProduct['auction_current_status'] !== 'upcoming')
                            <span class="d-flex align-items-center gap-1 title-clr fs-12">
                                <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/bid.svg') }}" alt="" class="svg">
                                {{ formatCompactNumber($auctionProduct->total_bids) }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <div>
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
                                                data-auction-product-id="{{ $auctionProduct->id }}"
                                                data-details-url="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}">
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
        </div>

        @if(isset($pageType) && $pageType == 'saved-page')
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
                        @if(!$cardIsOwner && $cardHasBid && $auctionProduct['auction_current_status'] == 'live')
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
                        <li>
                            <button type="button"
                                    class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1 js-remove-saved-auction"
                                    data-auction-product-id="{{ $auctionProduct->id }}"
                                    data-toggle-url="{{ route('auction.saved-products.toggle') }}">
                                {{ translate('Remove') }} <i class="fi fi-sr-trash text-danger"></i>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>
