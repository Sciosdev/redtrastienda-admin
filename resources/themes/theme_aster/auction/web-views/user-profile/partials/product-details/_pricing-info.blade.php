@php
    $showStartingPrice ??= true;
    $showMinIncrement  ??= true;
    $showMaxDecrement  ??= true;
    $showMinBidAmount  ??= true;
    $showShippingFee   ??= false;
    $showVatTax        ??= false;
    $minBidAmount      ??= 0;
    $labelClass        ??= 'minmax-xs-100px';
@endphp
<div class="p-15px card border-0 shadow-sm rounded">
    <div class="fs-14 fw-semibold title-clr mb-3">{{ translate('Pricing Info') }}</div>
    <div class="d-flex flex-column gap-15px">
        @if($showStartingPrice)
        <div class="d-flex align-items-center gap-2 justify-content-between w-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="{{ $labelClass }} fs-14 title-semidark">{{ translate('Starting Price') }}</div>
                <span>:</span>
            </div>
            <div class="info-option2">
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ webCurrencyConverter(amount: $auctionProduct?->starting_price) }}</h4>
            </div>
        </div>
        @endif
        @if($showMinIncrement)
        <div class="d-flex align-items-center gap-2 justify-content-between w-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="{{ $labelClass }} fs-14 title-semidark">{{ translate('Min Increment') }}</div>
                <span>:</span>
            </div>
            <div class="info-option2">
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ webCurrencyConverter(amount: $auctionProduct?->minimum_increment_amount) }}</h4>
            </div>
        </div>
        @endif
        @if($showMaxDecrement && !is_null($auctionProduct?->maximum_decrement_amount))
        <div class="d-flex align-items-center gap-2 justify-content-between w-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="{{ $labelClass }} fs-14 title-semidark">{{ translate('Max Decrement') }}</div>
                <span>:</span>
            </div>
            <div class="info-option2">
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ webCurrencyConverter(amount: $auctionProduct?->maximum_decrement_amount) }}</h4>
            </div>
        </div>
        @endif
        @if($showMinBidAmount)
        <div class="d-flex align-items-center gap-2 justify-content-between w-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="{{ $labelClass }} fs-14 title-semidark">{{ translate('Min Bid Amount') }}</div>
                <span>:</span>
            </div>
            <div class="info-option2">
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ webCurrencyConverter(amount: $minBidAmount) }}</h4>
            </div>
        </div>
        @endif
        @if($showShippingFee && $auctionProduct?->shipping_fee)
        <div class="d-flex align-items-center gap-2 justify-content-between w-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="{{ $labelClass }} fs-14 title-semidark">{{ translate('Shipping Fee') }}</div>
                <span>:</span>
            </div>
            <div class="info-option2">
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ webCurrencyConverter(amount: $auctionProduct?->shipping_fee) }}</h4>
            </div>
        </div>
        @endif
        @if($showVatTax && !empty($auctionProduct?->taxVats))
            @foreach($auctionProduct?->taxVats as $auctionTaxKey => $auctionTaxVats)
                @if(!empty($auctionTaxVats?->tax?->name))
                    <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="{{ $labelClass }} fs-14 title-semidark">
                                {{ $auctionTaxVats?->tax?->name }}
                            </div>
                            <span>:</span>
                        </div>
                        <div class="info-option2">
                            <h4 class="fs-14 m-0 title-clr fw-semibold">
                                {{ $auctionTaxVats?->tax?->tax_rate }}%
                            </h4>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</div>
