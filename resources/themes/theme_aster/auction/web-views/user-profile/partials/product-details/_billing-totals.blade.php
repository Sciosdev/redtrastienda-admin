@php
    $alwaysShowShippingFee ??= false;
    $showReturnPolicy      ??= false;
@endphp
<div class="p-15px card border-0 shadow-sm rounded">
    <div class="fs-14 fw-bold title-clr mb-15">{{ translate('Billing Info') }}</div>
    <div class="d-flex flex-column gap-15px">
        <div class="d-flex align-items-center gap-2 justify-content-between w-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="minmax-xl-130px fs-14 title-semidark">{{ translate('Product Price') }}</div>
                <span class="title-semidark">:</span>
            </div>
            <div class="info-option2">
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ webCurrencyConverter(amount: $claimProductPrice) }}</h4>
            </div>
        </div>
        @if($alwaysShowShippingFee || $claimShippingFee > 0)
        <div class="d-flex align-items-center gap-2 justify-content-between w-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="minmax-xl-130px fs-14 title-semidark">{{ translate('Shipping Fee') }}</div>
                <span class="title-semidark">:</span>
            </div>
            <div class="info-option2">
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ webCurrencyConverter(amount: $claimShippingFee) }}</h4>
            </div>
        </div>
        @endif
        @if($claimTaxAmount > 0)
        <div class="d-flex align-items-center gap-2 justify-content-between w-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="minmax-xl-130px fs-14 title-semidark">{{ translate('Tax') }}</div>
                <span class="title-semidark">:</span>
            </div>
            <div class="info-option2">
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ webCurrencyConverter(amount: $claimTaxAmount) }}</h4>
            </div>
        </div>
        @endif
        <div class="border-bottom"></div>
        <div class="d-flex align-items-center gap-2 justify-content-between w-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="minmax-xl-130px fs-14 title-semidark">{{ translate('Total') }}</div>
                <span class="title-semidark">:</span>
            </div>
            <div class="info-option2">
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ webCurrencyConverter(amount: $claimTotalAmount) }}</h4>
            </div>
        </div>
        @if($showReturnPolicy && !empty($auctionProduct?->return_policy))
        <div class="d-flex align-items-start gap-2 w-100">
            <div>
                <div class="fs-13 title-semidark mb-1">{{ translate('Return Policy') }}</div>
                <div class="fs-12 title-clr">{{ $auctionProduct->return_policy }}</div>
            </div>
        </div>
        @endif
    </div>
</div>
