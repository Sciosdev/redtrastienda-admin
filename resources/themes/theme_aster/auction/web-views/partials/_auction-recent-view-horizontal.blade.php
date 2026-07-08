@php
    $cardIsOwner      = auth('customer')->check() && $auctionProduct->owner_type === 'customer' && auth('customer')->id() == $auctionProduct->owner_id;
    $cardIsParticipant = auth('customer')->check() && $auctionProduct->myParticipation !== null;
    $cardHasBid       = auth('customer')->check() && $auctionProduct->myBid !== null;
@endphp
<div class="card border-0 shadow-sm rounded overflow-hidden position-relative">
    <div class="d-flex">
        <div class="position-relative flex-shrink-0" style="width:90px; min-width:90px;">

            @if($auctionProduct['auction_current_status'] == 'upcoming')
                <span class="badge bg-success rounded-0 rounded-4px position-absolute top-0 inline-start-0 px-1 py-1 z-1 lh-sm" style="font-size:10px;">
                    {{ translate('Upcoming') }}
                </span>
            @elseif($auctionProduct['auction_current_status'] == 'live')
                <span class="badge bg-danger rounded-0 rounded-4px position-absolute top-0 inline-start-0 px-1 py-1 z-1 lh-sm" style="font-size:10px;">
                    {{ translate('Live') }}
                </span>
            @elseif($auctionProduct['auction_current_status'] == 'unsold')
                <span class="badge bg-danger rounded-0 rounded-4px position-absolute top-0 inline-start-0 px-1 py-1 z-1 lh-sm" style="font-size:10px;">
                    {{ translate('Expired') }}
                </span>
            @endif

            <a href="{{ route('auction.product-details', ['slug' => $auctionProduct?->slug]) }}" class="d-block">
                <img src="{{ getStorageImages(path: $auctionProduct->thumbnail_full_url, type: 'product') }}"
                     alt="{{ $auctionProduct['name'] }}"
                     class="w-100 object-fit-cover"
                     style="height:90px;">
            </a>
        </div>

        <div class="py-2 px-2 w-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-start justify-content-between gap-1 mb-1">
                <a href="{{ route('auction.product-details', ['slug' => $auctionProduct?->slug]) }}"
                   class="text-decoration-none fw-semibold title-clr line--limit-2"
                   style="font-size:13px;">
                    {{ $auctionProduct['name'] }}
                </a>
                @if(auth('customer')->check())
                    <button type="button"
                            data-based-class="auction-saved-update-{{ $auctionProduct->id }}"
                            class="btn p-0 fs-14 outline-0 border-0 text-primary flex-shrink-0 auction-saved-update auction-saved-update-{{ $auctionProduct->id }} {{ $auctionProduct?->mySavedProduct ? 'is-saved' : '' }}"
                            data-auction-product-id="{{ $auctionProduct->id }}">
                        <i class="fi fi-rr-bookmark icon-unsaved"></i>
                        <i class="fi fi-sr-bookmark icon-saved"></i>
                    </button>
                @else
                    <button type="button"
                            class="btn p-0 fs-14 outline-0 border-0 text-primary flex-shrink-0"
                            data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fi fi-rr-bookmark"></i>
                    </button>
                @endif
            </div>

            <div class="mb-1" style="font-size:12px;">
                @if($auctionProduct?->current_highest_bid_amount > 0)
                    <span class="title-semidark">{{ translate('Highest Bid') }}: </span>
                    <strong class="text-primary">{{ webCurrencyConverter(amount: $auctionProduct->current_highest_bid_amount) }}</strong>
                @else
                    <span class="title-semidark">{{ translate('Start Price') }}: </span>
                    <strong class="text-primary">{{ webCurrencyConverter(amount: $auctionProduct->starting_price) }}</strong>
                @endif
            </div>

            <div class="d-flex justify-content-between align-items-center gap-1 flex-wrap">
                @if($auctionProduct['auction_current_status'] == 'upcoming' || $auctionProduct['auction_current_status'] == 'live')
                <div class="d-flex align-items-center gap-0 countdown auction-countdown"
                     style="font-size:11px; font-variant-numeric:tabular-nums;"
                     data-end="{{ $auctionProduct['auction_current_status'] == 'upcoming'
                        ? \Carbon\Carbon::parse($auctionProduct->start_time)->format('Y-m-d H:i:s')
                        : (\Carbon\Carbon::parse($auctionProduct->end_time)->isPast() ? \Carbon\Carbon::now()->format('Y-m-d H:i:s') : \Carbon\Carbon::parse($auctionProduct->end_time)->format('Y-m-d H:i:s')) }}">
                    <span class="time hours fw-semibold text-danger" style="display:inline-block;min-width:1.6em;text-align:right;">00</span><span class="text-danger">h</span>
                    <span class="time minutes fw-semibold text-danger" style="display:inline-block;min-width:1.6em;text-align:right;">00</span><span class="text-danger">m</span>
                    <span class="time seconds fw-semibold text-danger" style="display:inline-block;min-width:1.6em;text-align:right;">00</span><span class="text-danger">s</span>
                </div>
                @endif

                @if(!$cardIsOwner)
                    @if($auctionProduct['auction_current_status'] == 'upcoming')
                        @if($cardIsParticipant)
                            <button type="button" disabled
                                    class="btn bg-primary bg-opacity-10 text-primary rounded-pill btn-sm px-2 py-1 fw-bold lh-sm text-nowrap"
                                    style="font-size:11px;">
                                {{ translate('Participated') }}
                            </button>
                        @elseif(!auth('customer')->check())
                            <button type="button"
                                    class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold lh-sm text-nowrap"
                                    style="font-size:11px;"
                                    data-bs-toggle="modal" data-bs-target="#loginModal">
                                {{ translate('Participate') }}
                            </button>
                        @else
                            <button type="button"
                                    class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold lh-sm text-nowrap auction-participate-btn"
                                    style="font-size:11px;"
                                    data-url="{{ route('auction.participate.check') }}"
                                    data-auction-product-id="{{ $auctionProduct->id }}"
                                    data-details-url="{{ route('auction.product-details', ['slug' => $auctionProduct->slug]) }}">
                                {{ translate('Participate') }}
                            </button>
                        @endif
                    @elseif($auctionProduct['auction_current_status'] == 'live')
                        @if(!auth('customer')->check())
                            <button type="button"
                                    class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold lh-sm text-nowrap"
                                    style="font-size:11px;"
                                    data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fi fi-rr-auction" style="font-size:10px;"></i> {{ translate('Bid') }}
                            </button>
                        @elseif(!$cardIsParticipant)
                            <a href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug, 'open_participate' => 1]) }}"
                               class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold lh-sm text-nowrap"
                               style="font-size:11px;">
                                {{ translate('Participate') }}
                            </a>
                        @elseif(!$cardHasBid)
                            <a href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug, 'open_bid' => 1]) }}"
                               class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold lh-sm text-nowrap"
                               style="font-size:11px;">
                                <i class="fi fi-rr-auction" style="font-size:10px;"></i> {{ translate('Bid') }}
                            </a>
                        @else
                            <a href="{{ route('auction.product-details', ['slug' => $auctionProduct->slug, 'open_bid' => 1]) }}"
                               class="btn btn-primary rounded-pill btn-sm px-2 py-1 fw-bold lh-sm text-nowrap"
                               style="font-size:11px;">
                                <i class="fi fi-rr-auction" style="font-size:10px;"></i> {{ translate('Raise Bid') }}
                            </a>
                        @endif
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
