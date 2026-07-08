@if(isset($auctionProduct) && isset($applicableBidAmounts))
@php
    $minIncrement = (float) $auctionProduct?->minimum_increment_amount;
    $rollbackAvailable = !empty($canRollback);
    $minimumRollbackBid = (float) ($minimumAllowedRollbackBid ?? 0);
    $myCurrentBidAmount = (float) ($auctionProduct?->myBid?->bid_amount ?? 0);

    $nextRaiseAmount = $auctionProduct?->current_highest_bid_amount <= 0
        ? (float) $auctionProduct?->starting_price
        : (float) $auctionProduct?->current_highest_bid_amount + $minIncrement;

    $minimumRequired = $rollbackAvailable ? $minimumRollbackBid : $nextRaiseAmount;
    $defaultValue = collect($applicableBidAmounts)->isNotEmpty()
        ? (float) collect($applicableBidAmounts)->first()
        : $nextRaiseAmount;
    $currentDisplay = (float) $auctionProduct?->current_highest_bid_amount > 0
        ? (float) $auctionProduct->current_highest_bid_amount
        : null;
    $submitUrl = route('auction.bids.place');
    $submitLabel = translate('Place Bid');
    $submitInvalidMsg = translate('Please enter a valid bid amount.');
@endphp
<div class="modal-header border-0 justify-content-center pb-0 mb-3">
    <button type="button" class="position-absolute top-0 inline-end-0 m-2 btn p-0 text-secondary bg-light border rounded-circle d-center w-35px h-35px min-w-35px" data-bs-dismiss="modal" aria-label="Close">
        <i class="fi fi-rr-cross-small"></i>
    </button>
</div>
<div class="modal-body pt-0">
    @if(!is_null($currentDisplay))
        <div class="box mb-20 text-center">
            <h3 class="fw-bold mb-2">
                {{ webCurrencyConverter(amount: $currentDisplay) }}
            </h3>
            <p class="fs-14 pragraph-clr mb-0">
                {{ translate('Highest Bid') }}
            </p>
        </div>
    @endif

    <div class="light-box p-4 text-center rounded">
        <div class="box">
            <h3 class="fw-bold mb-10px fs-16">
                {{ translate('Enter an amount higher than the current bid') }}
            </h3>
            <div class="border bg-white rounded-1 max-w-280px mx-auto p-2 d-flex align-items-center gap-2">
                <button type="button" class="btn btn-light border text-danger d-center fs-16 fw-normal p-1 w-30px h-30px min-w-30px rounded flex-shrink-0 place-bid-decrement-btn"
                        data-amount="{{ webCurrencyConverterOnlyDigit(amount: $minIncrement) }}">
                    <i class="fi fi-sr-arrow-small-down"></i>
                </button>

                <div class="flex-grow-1">
                    <input type="text" class="form-control border-0 text-center outline-0 place-bid-amount-input"
                           placeholder="{{ webCurrencyConverter(amount: $minimumRequired) }}"
                           min="{{ webCurrencyConverterOnlyDigit(amount: $minimumRequired) }}"
                           value="{{ !is_null($defaultValue) ? webCurrencyConverterOnlyDigit(amount: $defaultValue) : '' }}"
                           inputmode="decimal"
                           oninput="this.value=this.value.replace(/[^0-9.]/g,'')">
                </div>

                <button type="button" class="btn btn-light border text-success d-center fs-16 fw-normal p-1 w-30px h-30px min-w-30px rounded flex-shrink-0 place-bid-increment-btn"
                        data-amount="{{ webCurrencyConverterOnlyDigit(amount: $minIncrement) }}">
                    <i class="fi fi-sr-arrow-small-up"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center align-items-center flex-wrap gap-xl-3 gap-2 mt-20">
        @foreach($applicableBidAmounts as $key => $applicableBidAmount)
            <button type="button" class="btn btn-light minimum-bid-btn place-bid-preset-btn fs-12 fw-normal py-2 px-12px rounded-pill {{ $key == 0 ? 'active' : '' }}"
                    data-amount="{{ webCurrencyConverterOnlyDigit(amount: $applicableBidAmount) }}">
                {{ webCurrencyConverter(amount: $applicableBidAmount) }}
            </button>
        @endforeach
    </div>

    @if($rollbackAvailable)
        <div class="mt-3 place-bid-rollback-info" style="display: none;">
            <div class="py-2 px-2 badge bg-warning title-semidark fw-normal bg-opacity-10 d-flex align-items-center gap-1 flex-wrap text-wrap text-start">
                <i class="fi fi-sr-info text-warning"></i>
                {{ translate('You are about to lower your winning bid to the minimum allowed amount') }}
                <strong>{{ webCurrencyConverter(amount: $minimumRollbackBid) }}</strong>.
                {{ translate('Rollback is one-time only and cannot be undone.') }}
            </div>
        </div>
    @endif
</div>
<div class="modal-footer justify-content-center border-0">
    <button type="button" class="btn btn-primary w-100 justify-content-center d-flex align-items-center gap-2 place-bid-submit-btn"
            data-url="{{ $submitUrl }}"
            data-auction-product-id="{{ $auctionProduct->id }}"
            data-label="{{ $submitLabel }}"
            data-update-label="{{ translate('Update Bid') }}"
            data-invalid-amount-message="{{ $submitInvalidMsg }}"
            data-rollback-available="{{ $rollbackAvailable ? '1' : '0' }}"
            data-error-message="{{ translate('Something went wrong. Please try again.') }}">
        <i class="fi fi-sr-auction"></i>
        <span class="place-bid-submit-label">{{ $submitLabel }}</span>
    </button>
</div>
@endif
