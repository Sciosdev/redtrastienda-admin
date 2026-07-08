<div>
    <div class="d-flex flex-column gap-15px">

        <div class="p-15px card border-0 shadow-sm rounded">
            @if(!empty($auctionProduct->tracking_url))
                <a href="{{ $auctionProduct->tracking_url }}"
                   target="_blank" rel="noopener noreferrer"
                   class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="fi fi-rr-location-arrow fs-14"></i>
                    {{ translate('Track Order') }}
                </a>
            @else
                <span class="d-block w-100" tabindex="0" data-bs-toggle="tooltip" data-bs-title="{{ translate('Tracking URL not provided yet') }}">
                    <button type="button" disabled class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2" style="pointer-events:none;">
                        <i class="fi fi-rr-location-arrow fs-14"></i>
                        {{ translate('Track Order') }}
                    </button>
                </span>
            @endif
        </div>

        {{-- Claim Note (only the winner reaches this partial) --}}
        @if(filled($auctionProduct?->claim_note))
            <div class="p-15px card border-0 shadow-sm rounded">
                <div class="fs-14 fw-bold title-clr mb-15 d-flex align-items-center gap-2">
                    <i class="fi fi-sr-comment-alt"></i>
                    {{ translate('Your Claim Note') }}
                </div>
                <p class="mb-0 fs-14 title-semidark overflow-wrap-anywhere" style="white-space: pre-wrap;">{{ $auctionProduct->claim_note }}</p>
            </div>
        @endif

        @if($claimPaymentMethod === 'offline_payment')
            <?php $offlineInfo = is_array($claimTransaction?->payment_info) ? $claimTransaction->payment_info : json_decode($claimTransaction?->payment_info ?? '{}', true); ?>
            @if(!empty($offlineInfo))
                <div class="p-15px card border-0 shadow-sm rounded">
                    <div class="fs-14 fw-bold title-clr mb-15">{{ translate('Offline Payment Info') }}</div>
                    <div class="d-flex flex-column gap-15px">
                        @foreach($offlineInfo as $key => $item)
                            @if(isset($item) && $key !== 'method_id' && $key !== 'payment_note' && !is_array($item))
                                <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="minmax-xl-130px fs-14 title-semidark text-capitalize">{{ translate(str_replace('_', ' ', $key)) }}</div>
                                        <span class="title-semidark">:</span>
                                    </div>
                                    <div class="info-option2">
                                        <h4 class="fs-14 m-0 title-clr fw-semibold">{{ $item }}</h4>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        @if(!empty($offlineInfo['method_informations']))
                            @foreach($offlineInfo['method_informations'] as $fieldKey => $fieldValue)
                                <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="minmax-xl-130px fs-14 title-semidark text-capitalize">{{ translate(str_replace('_', ' ', $fieldKey)) }}</div>
                                        <span class="title-semidark">:</span>
                                    </div>
                                    <div class="info-option2">
                                        <h4 class="fs-14 m-0 title-clr fw-semibold">{{ $fieldValue }}</h4>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        @if(!empty($offlineInfo['payment_note']))
                            <div class="d-flex align-items-start gap-2 justify-content-between w-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="minmax-xl-130px fs-14 title-semidark">{{ translate('Payment Note') }}</div>
                                    <span class="title-semidark">:</span>
                                </div>
                                <div class="info-option2">
                                    <p class="fs-14 m-0 title-clr">
                                        @if(strlen($offlineInfo['payment_note']) > 250)
                                            {{ Str::limit($offlineInfo['payment_note'], 250) }}
                                        @else
                                            {{ $offlineInfo['payment_note'] }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        @include('auction.web-views.user-profile.partials.product-details._payment-info')

        @include('auction.web-views.user-profile.partials.product-details._billing-totals', [
            'alwaysShowShippingFee' => false,
            'showReturnPolicy'      => false,
        ])

        @if(!empty($claimShippingInfo))
        <div class="p-15px card border-0 shadow-sm rounded">
            <div class="fs-14 fw-bold title-clr mb-15">{{ translate('Shipping address') }}</div>
            @include('auction.web-views.user-profile.partials.product-details._address-fields', ['info' => $claimShippingInfo])
        </div>
        @endif

        @if($claimIsBillingSame || !empty($claimBillingInfo))
        <div class="p-15px card border-0 shadow-sm rounded">
            <div class="fs-14 fw-bold title-clr mb-15">{{ translate('Billing address') }}</div>
            @if($claimIsBillingSame)
                <div class="bg-light rounded text-center fs-14 title-semidark py-3 px-3">{{ translate('Same As Shipping Address') }}</div>
            @else
                @include('auction.web-views.user-profile.partials.product-details._address-fields', ['info' => $claimBillingInfo])
            @endif
        </div>
        @endif

        <?php
            $claimedOwnerType  = $auctionProduct->owner_type;
            if ($claimedOwnerType === 'seller' && isset($auctionProduct->seller->shop)) {
                $claimedOwnerName  = $auctionProduct->seller->shop->name;
                $claimedOwnerImage = getStorageImages(path: $auctionProduct->seller->shop->image_full_url, type: 'shop');
                $claimedOwnerUrl   = route('vendor-shop', ['slug' => $auctionProduct->seller->shop->slug]);
            } elseif ($claimedOwnerType === 'admin') {
                $claimedOwnerName  = getInHouseShopConfig(key: 'name');
                $claimedOwnerImage = getStorageImages(path: getInHouseShopConfig(key: 'image_full_url'), type: 'shop');
                $claimedOwnerUrl   = route('vendor-shop', ['slug' => getInHouseShopConfig(key: 'slug')]);
            } else {
                $claimedOwnerName  = $auctionProduct->customer?->name ?? translate('Unknown');
                $claimedOwnerImage = getStorageImages(path: $auctionProduct->customer?->image_full_url, type: 'avatar');
                $claimedOwnerUrl   = null;
            }
        ?>
        @include('auction.web-views.user-profile.partials.product-details._owner-card', [
            'ownerName'         => $claimedOwnerName,
            'ownerImage'        => $claimedOwnerImage,
            'ownerUrl'          => $claimedOwnerUrl,
            'ownerLabel'        => translate('Auction Author'),
            'showMessageButton' => ($auctionProduct?->owner_type !== 'customer' && auth('customer')->id() != $auctionProduct?->owner_id),
        ])

        @include('auction.web-views.user-profile.partials.product-details._pricing-info', [
            'showStartingPrice' => true,
            'showMinIncrement'  => true,
            'showMaxDecrement'  => false,
            'showMinBidAmount'  => false,
            'showShippingFee'   => false,
            'showVatTax'        => false,
        ])

        @include('auction.web-views.user-profile.partials.product-details._auction-timeline')
    </div>
</div>
