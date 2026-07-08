<div>
    <div class="d-flex flex-column gap-15px">
        <div class="p-15px card border-0 shadow-sm rounded">
            <div class="fs-14 fw-bold title-clr mb-15">{{ translate('Payment Status') }}</div>
            <?php
                $paymentStatuses = [
                    \Modules\Auction\app\Enums\PaymentStatus::PAID => translate('Paid'),
                    \Modules\Auction\app\Enums\PaymentStatus::UNPAID => translate('Unpaid'),
                ];
            ?>
            <select name="payment_status"
                    class="form-select form-select-sm js-owner-payment-status"
                    data-auction-product-id="{{ $auctionProduct->id }}"
                    data-update-url="{{ route('auction.payment-status.update') }}"
                    data-current-status="{{ $claimPaymentStatus ?? \Modules\Auction\app\Enums\PaymentStatus::UNPAID }}"
                    @disabled($claimIsPaid || $claimPaymentMethod === 'offline_payment')>
                @foreach($paymentStatuses as $value => $label)
                    <option value="{{ $value }}" @selected($claimPaymentStatus === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @if($claimPaymentMethod === 'offline_payment' && !$claimIsPaid)
                <p class="fs-12 text-muted mt-1 mb-0">
                    <i class="fi fi-sr-lock fs-10"></i>
                    {{ translate('Payment status for offline payments can only be updated by admin') }}
                </p>
            @endif
        </div>

        <div class="p-15px card border-0 shadow-sm rounded">
            <div class="fs-14 fw-bold title-clr mb-15">{{ translate('Delivery Status') }}</div>
            <?php
                $deliveryStatuses = [
                    \Modules\Auction\app\Enums\DeliveryStatus::READY_TO_DELIVERY => translate('Ready_to_Delivery'),
                    \Modules\Auction\app\Enums\DeliveryStatus::ON_THE_WAY        => translate('On The Way'),
                    \Modules\Auction\app\Enums\DeliveryStatus::DELIVERED         => translate('Delivered'),
                ];
            ?>
            <select name="delivery_status"
                    class="form-select form-select-sm js-owner-delivery-status"
                    data-auction-product-id="{{ $auctionProduct->id }}"
                    data-update-url="{{ route('auction.delivery-status.update') }}"
                    data-current-status="{{ $claimDeliveryStatus ?: \Modules\Auction\app\Enums\AuctionStatus::PURCHASE_COMPLETE }}"
                    @disabled($claimDeliveryStatus === \Modules\Auction\app\Enums\DeliveryStatus::DELIVERED)>
                @if($claimDeliveryStatus === null)
                    <option value="{{ \Modules\Auction\app\Enums\AuctionStatus::PURCHASE_COMPLETE }}" selected disabled>
                        {{ translate('Purchase Complete') }}
                    </option>
                @endif
                @foreach($deliveryStatuses as $value => $label)
                    <option value="{{ $value }}" @selected($claimDeliveryStatus === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @if($claimDeliveryStatus === \Modules\Auction\app\Enums\DeliveryStatus::DELIVERED && $deliveredBreakdown)
        @if($claimPaymentMethod === 'cash_on_delivery')
            <?php $codTx = $codCommissionTransaction ?? null; $codTxStatus = $codTx?->payment_status; ?>
            @if($codTxStatus === \Modules\Auction\app\Enums\PaymentStatus::PAID)
                <div class="p-15px card border-0 shadow-sm rounded text-center">
                    <div class="mb-2"><i class="fi fi-rr-check-circle fs-28 text-success"></i></div>
                    <h4 class="fs-16 mb-1 text-success fw-semibold">{{ translate('Commission_Paid') }}</h4>
                    <h4 class="fs-16 mb-1 title-clr fw-semibold">{{ webCurrencyConverter(amount: $deliveredBreakdown->commissionAmount) }}</h4>
                    <p class="fs-12 title-clr mb-0">{{ translate('Your_commission_payment_has_been_completed_successfully.') }}</p>
                </div>
            @elseif($codTxStatus === \Modules\Auction\app\Enums\PaymentStatus::PENDING)
                <div class="p-15px card border-0 shadow-sm rounded text-center">
                    <div class="mb-2"><i class="fi fi-rr-clock fs-28 text-warning"></i></div>
                    <h4 class="fs-16 mb-1 text-warning fw-semibold">{{ translate('Payment_Under_Verification') }}</h4>
                    <h4 class="fs-16 mb-1 title-clr fw-semibold">{{ webCurrencyConverter(amount: $deliveredBreakdown->commissionAmount) }}</h4>
                    <p class="fs-12 title-clr mb-0">{{ translate('Your_commission_payment_is_pending_admin_verification.') }}</p>
                </div>
            @else
                <div class="p-15px card border-0 shadow-sm rounded text-center">
                    <h4 class="fs-16 mb-15 text-danger fw-semibold">{{ translate('Amount to pay Admin') }}</h4>
                    <h4 class="fs-16 mb-1 title-clr fw-semibold">{{ webCurrencyConverter(amount: $deliveredBreakdown->commissionAmount) }}</h4>
                    <p class="fs-12 title-clr">{{ translate('Your auction has ended successfully. Please complete the commission payment to close this transaction.') }}</p>
                    <button type="button" class="btn btn-primary w-100 max-w-180px mx-auto" data-bs-toggle="modal" data-bs-target="#auction-cod-commission-modal">
                        {{ translate('Pay Now') }}
                    </button>
                </div>
            @endif
        @else
            <?php $wr = $customerWithdrawRequest ?? null; ?>
            @if($wr && $wr->status === \Modules\Auction\app\Enums\WithdrawStatus::APPROVED)
                <div class="p-15px card border-0 shadow-sm rounded">
                    @if(!empty($wr->transaction_note))
                        <div class="badge py-3 px-3 bg-success w-100 text-start bg-opacity-10 rounded mb-15">
                            <h6 class="fs-13 fw-semibold title-clr mb-1">{{ translate('Approve_Note') }}</h6>
                            <p class="fs-12 mb-0 text-success fw-normal text-wrap">{{ $wr->transaction_note }}</p>
                        </div>
                    @endif
                    <div class="text-center mb-15">
                        <h4 class="fs-16 mb-1 text-success fw-semibold">{{ translate('Payment_Received_from_Admin') }}</h4>
                        <h4 class="fs-16 mb-1 title-clr fw-semibold">{{ webCurrencyConverter(amount: $wr->amount) }}</h4>
                        <p class="fs-12 title-clr mb-0">{{ translate('The admin has successfully completed the payment.') }}</p>
                    </div>
                    <div class="border-bottom mb-15"></div>
                    <h6 class="fs-13 fw-semibold title-clr mb-2">{{ translate('Withdraw_info') }}</h6>
                    @include('auction.web-views.user-profile.partials.product-details._withdraw-info-fields', ['fields' => $wr->withdrawal_method_fields ?? []])
                </div>
            @elseif($wr && $wr->status === \Modules\Auction\app\Enums\WithdrawStatus::PENDING)
                <div class="p-15px card border-0 shadow-sm rounded">
                    <div class="text-center mb-15">
                        <h4 class="fs-16 mb-1 text-danger fw-semibold">{{ translate('Amount_pay_by_Admin') }}</h4>
                        <h4 class="fs-16 mb-1 title-clr fw-semibold">{{ webCurrencyConverter(amount: $deliveredBreakdown->vendorReceivable) }}</h4>
                        <p class="fs-12 title-clr mb-0">{{ translate('The auction earnings will be transferred after deducting the admin commission.') }}</p>
                    </div>
                    <div class="border-bottom mb-15"></div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fs-13 fw-semibold title-clr mb-0">{{ translate('Withdraw_info') }}</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary p-1 lh-1" data-bs-toggle="offcanvas" data-bs-target="#auction-customer-withdraw-offcanvas">
                            <i class="fi fi-rr-pencil fs-12"></i>
                        </button>
                    </div>
                    @include('auction.web-views.user-profile.partials.product-details._withdraw-info-fields', ['fields' => $wr->withdrawal_method_fields ?? []])
                </div>
            @elseif($wr && in_array($wr->status, \Modules\Auction\app\Enums\WithdrawStatus::DENIED, true))
                <div class="p-15px card border-0 shadow-sm rounded">
                    @if(!empty($wr->transaction_note))
                        <div class="badge py-3 px-3 bg-danger w-100 text-start bg-opacity-10 rounded mb-15">
                            <h6 class="fs-13 fw-semibold title-clr mb-1">{{ translate('Denied_Note') }}</h6>
                            <p class="fs-12 mb-0 text-danger fw-normal text-wrap">{{ $wr->transaction_note }}</p>
                        </div>
                    @endif
                    <div class="text-center mb-15">
                        <h4 class="fs-16 mb-1 text-danger fw-semibold">{{ translate('Amount_pay_by_Admin') }}</h4>
                        <h4 class="fs-16 mb-1 title-clr fw-semibold">{{ webCurrencyConverter(amount: $deliveredBreakdown->vendorReceivable) }}</h4>
                        <p class="fs-12 title-clr mb-0">{{ translate('The auction earnings will be transferred after deducting the admin commission.') }}</p>
                    </div>
                    <div class="border-bottom mb-15"></div>
                    <h6 class="fs-13 fw-semibold title-clr mb-2">{{ translate('Withdraw_info') }}</h6>
                    <div class="mb-15">
                        @include('auction.web-views.user-profile.partials.product-details._withdraw-info-fields', ['fields' => $wr->withdrawal_method_fields ?? []])
                    </div>
                    <button type="button" class="btn btn-primary w-100"
                            data-bs-toggle="offcanvas" data-bs-target="#auction-customer-withdraw-offcanvas">
                        {{ translate('Withdraw_Money') }}
                    </button>
                </div>
            @else
                <div class="p-15px card border-0 shadow-sm rounded text-center">
                    <h4 class="fs-16 mb-15 text-danger fw-semibold">{{ translate('Amount_pay_by_Admin') }}</h4>
                    <h4 class="fs-16 mb-1 title-clr fw-semibold">{{ webCurrencyConverter(amount: $deliveredBreakdown->vendorReceivable) }}</h4>
                    <p class="fs-12 title-clr">{{ translate('The auction earnings will be transferred after deducting the admin commission.') }}</p>
                    <button type="button" class="btn btn-primary w-100"
                            data-bs-toggle="offcanvas" data-bs-target="#auction-customer-withdraw-offcanvas">
                        {{ translate('Withdraw_Money') }}
                    </button>
                </div>
            @endif
        @endif
        @endif

        @include('auction.web-views.user-profile.partials.product-details._billing-totals', [
            'alwaysShowShippingFee' => true,
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

        @if($auctionProduct->owner_type === \Modules\Auction\app\Enums\OwnerType::CUSTOMER
            && auth('customer')->check()
            && (int) $auctionProduct->owner_id === (int) auth('customer')->id())
            <form id="js-tracking-url-form" action="{{ route('auction.tracking-url.update', ['id' => $auctionProduct->id]) }}" method="POST" class="p-15px card border-0 shadow-sm rounded">
                @csrf
                <div class="fs-14 d-flex align-items-center gap-1 fw-bold title-clr mb-15">
                    {{ translate('Upload Tracking URL') }}
                    <span data-bs-toggle="tooltip" data-bs-title="{{ translate('Provide a tracking URL so the buyer can follow the shipment') }}">
                        <i class="fi fi-sr-info text-light-gray fs-12"></i>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-10px gap-15px">
                    <input type="url" name="tracking_url" class="form-control"
                           value="{{ old('tracking_url', $auctionProduct->tracking_url ?? '') }}"
                           placeholder="{{ translate('Ex : https://www.tracking.example/123') }}"
                           maxlength="2048">
                    <button type="submit" class="btn btn-primary fs-13 rounded px-3">
                        {{ translate('Save') }}
                    </button>
                </div>
            </form>

            @if(!empty($auctionProduct->tracking_url))
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex gap-3 align-items-center justify-content-between">
                            <h6 class="mb-0 fs-14 text-capitalize fw-semibold title-clr line--limit-1">
                                {{ translate('Track Order') }}
                            </h6>
                            <a href="{{ $auctionProduct->tracking_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary py-2 fs-12 px-3">
                                {{ translate('Track') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @include('auction.web-views.user-profile.partials.product-details._payment-info')

        @include('auction.web-views.user-profile.partials.product-details._bidding-info', [
            'totalBidsValue'  => $auctionProduct->total_bids,
            'totalViewsValue' => $auctionProduct->total_views,
            'feeProvideValue' => $auctionProduct->participants_count,
        ])

        @include('auction.web-views.user-profile.partials.product-details._auction-duration')

        @include('auction.web-views.user-profile.partials.product-details._pricing-info', [
            'showStartingPrice' => true,
            'showMinIncrement'  => true,
            'showMaxDecrement'  => false,
            'showMinBidAmount'  => true,
            'showShippingFee'   => false,
            'showVatTax'        => false,
            'minBidAmount'      => $claimMinBidAmount,
        ])

        @include('auction.web-views.user-profile.partials.product-details._winner-card')

        @include('auction.web-views.user-profile.partials.product-details._owner-card', [
            'ownerName'         => $claimOwnerName,
            'ownerImage'        => $claimOwnerImage,
            'ownerUrl'          => $claimOwnerUrl,
            'ownerLabel'        => $claimOwnerLabel,
            'showMessageButton' => false,
        ])

        @include('auction.web-views.user-profile.partials.product-details._auction-timeline')
    </div>
</div>
