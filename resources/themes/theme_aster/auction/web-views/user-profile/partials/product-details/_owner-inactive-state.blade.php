<?php
    // Shared owner-perspective layout for the three pre-active auction states:
    //   - pending  → awaiting admin approval
    //   - rejected → admin denied (optionally with rejected_note)
    //   - canceled → owner canceled before activation
    //
    // Card structure is identical across all three; only the sidebar note card
    // and the header action buttons vary. Bid/view counts are intentionally
    // omitted — they only become meaningful from UPCOMING onward.
?>
<div>
    <div class="bid-product-details__wrapper d-flex gap-3">
        <div class="bid-product-details-body">
            <div class="card border-0 shadow-sm p-xxl-20px p-3 mb-20">
                <div class="row g-3">
                    <div class="col-sm-4">
                        @include('auction.web-views.user-profile.partials.product-details._image-slider')
                    </div>
                    <div class="col-sm-8">
                        <div class="">
                            <div class="fs-12 title-semidark fw-medium mb-1">
                                {{ translate('Auction') }} {{ translate('ID') .' #'. $auctionProduct['id'] }}
                            </div>
                            <h2 class="mb-12px fs-16 line--limit-2 title-clr fw-bold">
                                {{ $auctionProduct['name'] }}
                            </h2>

                            <div class="d-flex flex-column gap-2 auction-details-right-content">
                                @if(!empty($auctionProduct['item_condition']))
                                    <div class="d-flex align-items-center gap-10px">
                                        <div class="minmax-xs-100px fs-14 title-semidark">{{ translate('Item condition') }}</div>
                                        <span>:</span>
                                        <div class="info-option2">
                                            <h4 class="fs-14 m-0 title-clr fw-semibold">{{ str_replace('_', ' ', $auctionProduct['item_condition']) }}</h4>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($auctionProduct?->brand?->name))
                                    <div class="d-flex align-items-center gap-10px">
                                        <div class="minmax-xs-100px fs-14 title-semidark">{{ translate('Brand') }}</div>
                                        <span>:</span>
                                        <div class="info-option2">
                                            <h4 class="fs-14 m-0 title-clr fw-semibold">{{ $auctionProduct?->brand?->name }}</h4>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($auctionProduct?->category?->name))
                                    <div class="d-flex align-items-center gap-10px">
                                        <div class="minmax-xs-100px fs-14 title-semidark">{{ translate('Category') }}</div>
                                        <span>:</span>
                                        <div class="info-option2">
                                            <h4 class="fs-14 m-0 title-clr fw-semibold">{{ $auctionProduct?->category?->name }}</h4>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($auctionProduct?->return_policy))
                                    <div class="d-flex align-items-start gap-10px">
                                        <div class="minmax-xs-100px fs-14 title-semidark">{{ translate('Return Policy') }}</div>
                                        <span>:</span>
                                        <div class="info-option2">
                                            <h4 class="fs-14 m-0 title-clr fw-semibold line--limit-2 text-break" data-bs-toggle="tooltip" data-bs-title="{{ $auctionProduct?->return_policy }}">{{ $auctionProduct?->return_policy }}</h4>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if($inactiveStateActions === 'delete-edit')
                                <div class="mt-20 d-flex align-items-center gap-2 flex-nowrap">
                                    <button type="button" class="btn bg-danger bg-opacity-10 fs-14 px-3 fw-semibold text-danger js-delete-auction-btn" data-auction-id="{{ $auctionProduct->id }}">
                                        {{ translate('Delete Auction') }}
                                    </button>
                                    <a href="{{ route('auction.auction-update-product', $auctionProduct->id) }}" class="btn btn-primary fs-14 px-3">
                                        {{ translate('Edit Auction') }}
                                    </a>
                                </div>
                            @elseif($inactiveStateActions === 'delete-recreate')
                                <div class="mt-20 d-flex align-items-center gap-2 flex-nowrap">
                                    <button type="button" class="btn bg-danger bg-opacity-10 fs-14 px-3 fw-semibold text-danger js-delete-auction-btn" data-auction-id="{{ $auctionProduct->id }}">
                                        {{ translate('Delete Auction') }}
                                    </button>
                                    <a href="{{ route('auction.auction-recreate-product', $auctionProduct->id) }}" class="btn btn-primary fs-14 px-3">
                                        {{ translate('Recreate Auction') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm p-xl-4 p-3">
                <div class="tab-content" id="overview-pills-tabContent">
                    @php
                        $hasVideo = ($auctionProduct?->video_provider === 'youtube_link'
                            && $auctionProduct?->youtube_embed_url != null
                            && str_contains($auctionProduct?->youtube_embed_url, "youtube.com/embed/"))
                            || ($auctionProduct?->video_provider === 'custom_video' && $auctionProduct?->custom_video_url);
                    @endphp
                    <ul class="nav nav-pills mb-20 d-flex align-items-center justify-content-center gap-1">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" type="button" id="overview-tab" data-bs-toggle="pill" data-bs-target="#des_overview" type="button" role="tab">
                                {{ translate('Overview') }}
                            </button>
                        </li>
                        @if($hasVideo)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="inactive-profile-video-tab" data-bs-toggle="pill"
                                    data-bs-target="#inactive-profile-video" type="button" role="tab"
                                    aria-controls="inactive-profile-video" aria-selected="false">
                                {{ translate('Video') }}
                            </button>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-pane fade show active" id="des_overview" role="tabpanel" aria-labelledby="overview-tab" tabindex="0">
                        @include('auction.web-views.user-profile.partials.product-details._description-block')
                    </div>
                    @if($hasVideo)
                    <div class="tab-pane fade" id="inactive-profile-video" role="tabpanel"
                         aria-labelledby="inactive-profile-video-tab" tabindex="0">
                        @if($auctionProduct?->video_provider === 'youtube_link')
                            <div class="col-12 mb-4">
                                <iframe width="420" height="315" src="{{ $auctionProduct?->youtube_embed_url }}"
                                        title="{{ translate('YouTube video player') }}" frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                            </div>
                        @elseif($auctionProduct?->video_provider === 'custom_video')
                            <div>
                                <video class="js-auction-product-player" playsinline controls
                                       data-poster="{{ getStorageImages(path: $auctionProduct?->seo?->image_full_url, type: 'product') }}">
                                    <source src="{{ getStorageImages(path: $auctionProduct->custom_video_url_full_url, type: 'product') }}" type="video/mp4">
                                </video>
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
                @include('auction.web-views.user-profile.partials.product-details._seo-meta')
            </div>
        </div>

        <div class="bids-product-details-right">
            <div class="d-flex flex-column gap-15px">
                @if($inactiveStateShowStatusSelect)
                    @include('auction.web-views.user-profile.partials.product-details._status-select')
                @endif

                @if($inactiveStateShowDeniedNote)
                    <div class="p-15px card border-0 shadow-sm rounded">
                        <div class="fs-14 fw-semibold title-clr text-center mb-3">{{ translate('Denied Note') }}</div>
                        <div class="bg-danger bg-opacity-10 rounded w-100 py-2 px-3 fs-14 fw-normal text-danger">
                            {{ $auctionProduct->rejected_note }}
                        </div>
                    </div>
                @endif

                @if(!empty($inactiveStateNote))
                    <div class="p-15px card border-0 shadow-sm rounded">
                        <div class="bg-{{ $inactiveStateNote['variant'] }} bg-opacity-10 rounded text-center py-12px px-3">
                            <h4 class="fs-16 fw-semibold text-{{ $inactiveStateNote['variant'] }} mb-1">{{ $inactiveStateNote['title'] }}</h4>
                            <p class="fs-14 title-semidark mb-0">{{ $inactiveStateNote['message'] }}</p>
                        </div>
                    </div>
                @endif

                @include('auction.web-views.user-profile.partials.product-details._auction-duration')
                @include('auction.web-views.user-profile.partials.product-details._pricing-info', [
                    'showStartingPrice' => true,
                    'showMinIncrement'  => true,
                    'showMaxDecrement'  => true,
                    'showMinBidAmount'  => true,
                    'showShippingFee'   => true,
                    'showVatTax'        => true,
                    'minBidAmount'      => $pendingMinBidAmount,
                ])
                @include('auction.web-views.user-profile.partials.product-details._auction-timeline')
            </div>
        </div>
    </div>
</div>
