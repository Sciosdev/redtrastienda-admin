@php
    use Modules\Auction\app\Enums\ApprovalStatus;
    use Modules\Auction\app\Enums\AuctionStatus;
    use Modules\Auction\app\Enums\ProductStatus;
    $timerEnd = $auctionProduct?->auction_current_status === 'upcoming'
        ? $auctionProduct?->start_time?->format('Y-m-d H:i:s')
        : $auctionProduct?->end_time?->format('Y-m-d H:i:s');
@endphp
<div>
    <div class="d-flex flex-column gap-15px">
        {{-- Status-select visible for pending (via _owner-inactive-state) and upcoming only. --}}
        @if($isOwner && $auctionProduct?->approval_status == ApprovalStatus::APPROVED && in_array($auctionProduct?->auction_current_status, [AuctionStatus::UPCOMING, AuctionStatus::LIVE]))
            @include('auction.web-views.user-profile.partials.product-details._status-select')
        @endif

        @if($auctionProduct?->auction_current_status == 'upcoming')
            @include('auction.web-views.user-profile.partials.product-details._countdown', [
                'countdownLabel' => translate('Auction Starts In'),
                'countdownEnd'   => $timerEnd,
            ])
        @elseif($auctionProduct?->auction_current_status == 'live')
            @include('auction.web-views.user-profile.partials.product-details._countdown', [
                'countdownLabel' => translate('Auction Ends In'),
                'countdownEnd'   => $timerEnd,
            ])
        @elseif($auctionProduct?->auction_current_status == 'unsold')
            <?php
                $isUnsoldWithBids   = ($isOwner ?? false) && (int)($auctionProduct->total_bids ?? 0) > 0;
                $unsoldMinBidAmount = ($auctionProduct->starting_price ?? 0) + ($auctionProduct->minimum_increment_amount ?? 0);
            ?>
            @if($isUnsoldWithBids)
                <div class="p-15px card border-0 shadow-sm rounded">
                    <div class="bg-danger bg-opacity-10 rounded text-center py-12px px-3">
                        <h4 class="fs-16 fw-semibold text-danger mb-1">{{ translate('Auction Ended – Item Not Sold') }}</h4>
                        <p class="fs-14 title-semidark mb-0">{{ translate('Update the details and relist to create the auction again.') }}</p>
                    </div>
                </div>
                @include('auction.web-views.user-profile.partials.product-details._bidding-info', [
                    'totalBidsValue'  => $auctionProduct->total_bids,
                    'totalViewsValue' => $auctionProduct->total_views,
                    'feeProvideValue' => $auctionProduct->total_participants,
                ])
                @include('auction.web-views.user-profile.partials.product-details._pricing-info', [
                    'showStartingPrice' => true,
                    'showMinIncrement'  => true,
                    'showMaxDecrement'  => false,
                    'showMinBidAmount'  => true,
                    'showShippingFee'   => false,
                    'showVatTax'        => false,
                    'minBidAmount'      => $unsoldMinBidAmount,
                    'labelClass'        => 'minmax-sm-120px',
                ])
            @else
                <div class="p-15px card border-0 shadow-sm rounded">
                    <div class="bg-danger bg-opacity-10 rounded text-center py-12px px-3">
                        <h4 class="fs-16 fw-semibold text-danger mb-0">{{ translate('Auction is Expired') }}</h4>
                    </div>
                </div>
            @endif
        @elseif($ownerClaimExpired ?? false)
            <div class="p-15px card border-0 shadow-sm rounded">
                <div class="bg-danger bg-opacity-10 rounded text-center py-12px px-3 mb-3">
                    <h4 class="fs-16 fw-semibold text-danger mb-1">{{ translate('Claim Period Expired') }}</h4>
                    <p class="fs-14 title-semidark mb-0">{{ translate('The winner did not claim within the allowed time. You may delete this auction.') }}</p>
                </div>
                <button type="button"
                        class="btn bg-danger bg-opacity-10 w-100 fs-16 fw-semibold text-danger js-delete-auction-btn"
                        data-auction-id="{{ $auctionProduct->id }}">
                    {{ translate('Delete Auction') }}
                </button>
            </div>
        @else
            <?php
                $myAuctionStatus = $auctionProduct?->my_auction_status;
                $isHighestBidder = $auctionProduct->highestBid
                    && (int) $auctionProduct->highestBid->user_id === (int) auth('customer')->id();
            ?>
            @if($myAuctionStatus === \Modules\Auction\app\Enums\AuctionStatus::FINALIZING)
                <div class="p-15px card border-0 shadow-sm rounded">
                    <div class="bg-warning bg-opacity-10 rounded text-center py-12px px-3">
                        <h4 class="fs-16 fw-semibold text-warning mb-1">{{ translate('Auction ended — finalizing results') }}</h4>
                        <p class="fs-14 title-semidark mb-0">{{ translate('The winner is being determined. Please check back shortly.') }}</p>
                    </div>
                </div>
            @elseif($myAuctionStatus === 'claim_expired_lost' && $isHighestBidder)
                <div class="p-15px card border-0 shadow-sm rounded">
                    <div class="bg-danger bg-opacity-10 rounded text-center py-12px px-3">
                        <h4 class="fs-16 fw-semibold text-danger mb-1">{{ translate('Claim Expired') }}</h4>
                        <p class="fs-14 title-semidark mb-0">{{ translate('You won but did not claim within the allowed time.') }}</p>
                    </div>
                </div>
            @elseif(in_array($myAuctionStatus, ['lost', 'claim_expired_lost']))
                <div class="p-15px card border-0 shadow-sm rounded">
                    <div class="bg-danger bg-opacity-10 rounded text-center py-12px px-3">
                        <h4 class="fs-16 fw-semibold text-danger mb-1">{{ translate('Auction Lost') }}</h4>
                        <p class="fs-14 title-semidark mb-0">{{ translate('You did not place the winning bid for this auction.') }}</p>
                    </div>
                </div>
            @endif
        @endif

        {{-- REJECTED / CANCELED pricing block was here — moved into _owner-inactive-state. --}}

        @if($auctionProduct?->auction_current_status === \Modules\Auction\app\Enums\AuctionStatus::READY_TO_CLAIM && !$isOwner && !$auctionProduct?->is_claim_expired_loser && $auctionProduct?->winner_user_id === auth('customer')->id())
        <div class="countdown-card p-15px card border-0 shadow-sm rounded">
            <div class="text-center">
                @if(!empty($auctionProduct?->is_winner_rollover))
                <div class="mb-20">
                    <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/congrt-bid.png') }}" alt="" class="w-70 h-70 rounded-circle mb-20 mx-auto border">
                    <h4 class="fs-20 fw-semibold text-success title-clr mb-10px">{{ translate('Great News!') }} 🎉</h4>
                    <p class="fs-14 title-semidark mb-0 text-center">
                        {{ translate('The previous winner missed the payment deadline. You are now the winning bidder! Complete your payment to claim your product.') }}
                    </p>
                </div>
                @else
                <div class="mb-20">
                    <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/congrt-bid.png') }}" alt="" class="w-70 h-70 rounded-circle mb-20 mx-auto border">
                    <h4 class="fs-20 fw-semibold text-success title-clr mb-10px">{{ translate('Congratulations!') }} 🎉</h4>
                    <p class="fs-14 title-semidark mb-0 text-center">
                        {{ translate("You’re the winning bidder! Complete your payment to claim your product.") }}
                    </p>
                </div>
                @endif
                <div class="light-box rounded p-xl-3 p-2">
                    @if(!empty($claimTimeLimitEnabled) && !empty($claimEndTime))
                            <span class="cz-countdown mb-10px flash-deal-countdown d-flex justify-content-center align-items-center gap-10px" data-countdown="{{ $claimEndTime }}">
                                <span class="cz-countdown-days">
                                    <span class="cz-countdown-value m-value d-center text-white fw-bold fs-20 rounded"></span>
                                    <span class="cz-countdown-text text-nowrap fs-12 fw-bold title-clr">{{ translate('days')}}</span>
                                </span>
                                <span class="cz-countdown-value pb-4 fs-16 text-primary fw-bold">:</span>
                                <span class="cz-countdown-hours">
                                    <span class="cz-countdown-value m-value d-center text-white fw-bold fs-20 rounded"></span>
                                    <span class="cz-countdown-text text-nowrap fs-12 fw-bold title-clr">{{ translate('hours')}}</span>
                                </span>
                                <span class="cz-countdown-value pb-4 fs-16 text-primary fw-bold">:</span>
                                <span class="cz-countdown-minutes">
                                    <span class="cz-countdown-value m-value d-center text-white fw-bold fs-20 rounded"></span>
                                    <span class="cz-countdown-text text-nowrap fs-12 fw-bold title-clr">{{ translate('minutes')}}</span>
                                </span>
                                <span class="cz-countdown-value pb-4 fs-16 text-primary fw-bold">:</span>
                                <span class="cz-countdown-seconds">
                                    <span class="cz-countdown-value m-value d-center text-white fw-bold fs-20 rounded"></span>
                                    <span class="cz-countdown-text text-nowrap fs-12 fw-bold title-clr">{{ translate('seconds')}}</span>
                                </span>
                            </span>
                    <p class="fs-14 title-semidark mb-0 text-center">
                        {!! translate("Claim your product before time runs out. Click <strong>'Claim Product'</strong> to complete payment.") !!}
                    </p>
                    @else
                    <p class="fs-14 title-semidark mb-0 text-center">
                        {!! translate("No time limit to claim. Click <strong>'Claim Product'</strong> anytime to complete payment.") !!}
                    </p>
                    @endif
                </div>
                @if(!empty($isClaimEligible))
                <div class="mt-20 max-w-180px w-100 mx-auto">
                    <a href="{{ route('auction.claim.checkout', ['auctionProductId' => $auctionProduct->id]) }}" class="btn btn-primary w-100">
                        {{ translate('Claim Product') }}
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if(!$isOwner && !in_array($auctionProduct?->auction_current_status, [
            \Modules\Auction\app\Enums\AuctionStatus::READY_TO_CLAIM,
            \Modules\Auction\app\Enums\AuctionStatus::LIVE,
            \Modules\Auction\app\Enums\AuctionStatus::UPCOMING,
            \Modules\Auction\app\Enums\AuctionStatus::PURCHASE_COMPLETE,
            \Modules\Auction\app\Enums\AuctionStatus::READY_TO_DELIVERY,
            \Modules\Auction\app\Enums\AuctionStatus::ON_THE_WAY,
            \Modules\Auction\app\Enums\AuctionStatus::DELIVERED,
        ]) && !empty($auctionProduct?->winner_user_id) && $auctionProduct?->winner_user_id !== auth('customer')->id())
        <div class="p-15px card border-0 shadow-sm rounded">
            <div class="bg-danger bg-opacity-10 rounded text-center py-12px px-3">
                <h4 class="fs-16 fw-semibold text-danger mb-1">{{ translate("Oops! Time’s Up") }}</h4>
                <p class="fs-14 title-semidark mb-0">{{ translate('You missed the time limit to place your order.') }}</p>
            </div>
        </div>
        @endif

        {{-- Auction Duration: owner → all states, customer → upcoming/live only --}}
        @if($isOwner || in_array($auctionProduct?->auction_current_status, [
            \Modules\Auction\app\Enums\AuctionStatus::UPCOMING,
            \Modules\Auction\app\Enums\AuctionStatus::LIVE,
        ]))
            @include('auction.web-views.user-profile.partials.product-details._auction-duration')
        @endif

        @if($auctionProduct?->winner_user_id != auth('customer')->id() || $auctionProduct?->auction_current_status === \Modules\Auction\app\Enums\AuctionStatus::READY_TO_CLAIM)
            <div class="p-15px card border-0 shadow-sm rounded">
                <div class="fs-14 fw-semibold title-clr">{{ translate('Product Info') }}</div>
                <div>

                    @if(!empty($auctionProduct['item_condition']))
                    <div class="d-flex align-items-center gap-2 justify-content-between w-100 border-bottom py-12px">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="minmax-xs-100px fs-14 title-semidark">
                                {{ translate('Item condition') }}
                            </div>
                            <span>:</span>
                        </div>
                        <div class="info-option2">
                            <h4 class="fs-16 m-0 title-clr fw-semibold">
                                {{ str_replace('_', ' ', $auctionProduct['item_condition']) }}
                            </h4>
                        </div>
                    </div>
                    @endif

                    @if(!empty($auctionProduct?->brand?->name))
                    <div class="d-flex align-items-center gap-2 justify-content-between w-100 border-bottom py-12px">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="minmax-xs-100px fs-14 title-semidark">
                                {{ translate('Brand') }}
                            </div>
                            <span>:</span>
                        </div>
                        <div class="info-option2">
                            <h4 class="fs-16 m-0 title-clr fw-semibold">
                                {{ $auctionProduct?->brand?->name }}
                            </h4>
                        </div>
                    </div>
                    @endif

                    @if($auctionProduct?->category?->name)
                    <div class="d-flex align-items-center gap-2 justify-content-between w-100 border-bottom py-12px">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="minmax-xs-100px fs-14 title-semidark">
                                {{ translate('Category') }}
                            </div>
                            <span>:</span>
                        </div>
                        <div class="info-option2">
                            <h4 class="fs-16 m-0 title-clr fw-semibold">
                                {{ $auctionProduct?->category?->name }}
                            </h4>
                        </div>
                    </div>
                    @endif

                    @if(!empty($auctionProduct?->taxVats))
                        @foreach($auctionProduct?->taxVats as $auctionTaxKey => $auctionTaxVats)
                            @if(!empty($auctionTaxVats?->tax?->name))
                                <div class="d-flex align-items-center gap-2 justify-content-between w-100 border-bottom py-12px">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="minmax-xs-100px fs-14 title-semidark">
                                            {{ $auctionTaxVats?->tax?->name }}
                                        </div>
                                        <span>:</span>
                                    </div>
                                    <div class="info-option2">
                                        <h4 class="fs-16 m-0 title-clr fw-semibold">
                                            {{ $auctionTaxVats?->tax?->tax_rate }}%
                                        </h4>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
            @include(VIEW_FILE_NAMES['frontend_auction_profile_auction_info'], [
                'auctionProduct' => $auctionProduct,
                'isOwner' => $isOwner,
                'isParticipant' => $isParticipant,
                'raiseBidAmount' => $raiseBidAmount,
                'nextBidAmount' => $nextBidAmount,
            ])
        @endif

        @include('auction.web-views.user-profile.partials.product-details._offline-payment-card')

        <?php
            $ownerType         = $auctionProduct->owner_type;
            $ownerLabel        = translate('Auction Author');
            $authorAuctionsUrl = route('auction.products.author', ['author_type' => $auctionProduct->owner_type, 'author_id' => $auctionProduct->owner_id]);
            if ($ownerType === 'seller' && isset($auctionProduct->seller->shop)) {
                $ownerName  = $auctionProduct->seller->shop->name;
                $ownerImage = getStorageImages(path: $auctionProduct->seller->shop->image_full_url, type: 'shop');
                $ownerUrl   = $authorAuctionsUrl;
            } elseif ($ownerType === 'admin') {
                $ownerName  = getInHouseShopConfig(key: 'name');
                $ownerImage = getStorageImages(path: getInHouseShopConfig(key: 'image_full_url'), type: 'shop');
                $ownerUrl   = $authorAuctionsUrl;
            } else {
                $ownerName  = $auctionProduct->customer?->name ?? translate('Unknown');
                $ownerImage = getStorageImages(path: $auctionProduct->customer?->image_full_url, type:'avatar');
                $ownerUrl   = $authorAuctionsUrl;
            }
        ?>
        @include('auction.web-views.user-profile.partials.product-details._owner-card', [
            'ownerName'         => $ownerName,
            'ownerImage'        => $ownerImage,
            'ownerUrl'          => $ownerUrl,
            'ownerLabel'        => $ownerLabel,
            'showMessageButton' => ($ownerType !== 'customer' && auth('customer')->id() != $auctionProduct?->owner_id),
        ])

        @include('auction.web-views.user-profile.partials.product-details._auction-timeline')
    </div>
</div>
