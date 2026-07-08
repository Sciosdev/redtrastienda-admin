@extends("auction.layouts.auction-app")

@section('title', translate('Auction Claim Checkout'))

@section('content')
<?php $defaultLocation = getWebConfig(name: 'default_location'); ?>
<div class="container py-4">

    <div class="mb-3">
        <h3 class="fs-18 fw-semibold title-clr">{{ translate('Shopping cart') }}</h3>
    </div>

    <form action="{{ route('auction.claim.process') }}" method="POST" id="auction-claim-form">
        @csrf
        <input type="hidden" name="auction_product_id" value="{{ $auctionProduct->id }}">
        <input type="hidden" name="payment_platform" value="web">
        <input type="hidden" name="current_currency_code" value="{{ session('currency_code') ?? getCurrencyCode() }}">
        <input type="hidden" name="external_redirect_link"
               value="{{ route('auction.claim.payment.success', $auctionProduct->slug) }}">
        <input type="hidden" name="amount" value="{{ $totalPayable }}">
        <input type="hidden" name="total_amount" value="{{ $totalPayable }}">
        <input type="hidden" name="billing_same_as_shipping" id="billing_same_as_shipping_input" value="1">

        <div class="row g-3">

            <div class="col-lg-8">

                <div class="card bs-border rounded overflow-hidden shadow-sm mb-3">
                    <div class="cards-title fs-18 border-bottom d-flex align-items-center gap-5px fw-semibold light-box py-12px px-xxl-20 px-3">
                        <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/vallery-shop.png') }}" alt="" class="">
                        {{ $web_config['company_name'] }}
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-borderless mb-0">
                                <thead class="table-header-bg">
                                    <tr>
                                        <th><div class="fs-13 fw-semibold">{{ translate('Item Info') }}</div></th>
                                        <th><div class="fs-13 fw-semibold">{{ translate('Starting Price') }}</div></th>
                                        <th class="text-end"><div class="fs-13 fw-semibold">{{ translate('Winning Bid') }}</div></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-sm-center gap-2">
                                                <a href="{{ route('auction.product-details', $auctionProduct->slug) }}"
                                                   class="w-60px h-60px min-w-60px d-center rounded overflow-hidden border">
                                                    <img src="{{ getStorageImages(path: $auctionProduct->thumbnail_full_url, type: 'backend-product') }}"
                                                         alt="{{ $auctionProduct->name }}" class="w-100 h-100 object-fit-cover">
                                                </a>
                                                <div>
                                                    <a href="{{ route('auction.product-details', $auctionProduct->slug) }}" class="text-decoration-none">
                                                        <h6 class="mb-1 fs-13 line--limit-2 title-clr fw-semibold">
                                                            {{ $auctionProduct->name }}
                                                        </h6>
                                                    </a>
                                                    <div class="d-flex flex-column gap-1">
                                                        @if($auctionProduct->category)
                                                            <span class="fs-12 title-semidark">
                                                                {{ translate('Category') }}: <span class="text-dark">{{ $auctionProduct->category->name }}</span>
                                                            </span>
                                                        @endif
                                                        @if($auctionProduct->item_condition)
                                                            <span class="fs-12 title-semidark">
                                                                {{ translate('Condition') }}: <span class="text-dark">{{ $auctionProduct->item_condition }}</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fs-13 title-clr">
                                                {{ webCurrencyConverter(amount: $auctionProduct->starting_price) }}
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="fs-14 fw-bold title-clr">
                                                {{ webCurrencyConverter(amount: $bidAmount) }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mb-20">
                    <h3 class="fs-16 mb-10px fw-semibold title-clr">{{ translate('Claim Product Note (Optional)') }}</h3>
                    <div class="form-group m-0">
                        <textarea class="form-control" name="claim" id="" placeholder="{{ translate('Type your note here') }}" rows="2"></textarea>
                    </div>
                </div>

                @if($auctionProduct->product_type === 'physical')
                <div class="card bs-border shadow-sm mb-3" id="shipping-address-section">
                    <div class="card-header light-box border-0 py-12px px-xxl-20 px-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fs-16 fw-semibold">{{ translate('Shipping Address') }}</h5>
                            @if($shipping_addresses->isNotEmpty())
                            <select class="form-select outline-0 shadow-none w-auto py-2 bg-white bs-border fs-12" id="claim-saved-address-select">
                                <option value="">{{ translate('Saved Address') }}</option>
                                @foreach($shipping_addresses as $addr)
                                    <option value="{{ json_encode($addr->only(['contact_person_name','phone','address','city','zip','country'])) }}">
                                        {{ ucfirst($addr->address_type) }}{{ $addr->address ? ' — '.\Illuminate\Support\Str::limit($addr->address, 28) : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label mb-1 fs-13">{{ translate('Contact Person Name') }}</label>
                                <input type="text" class="form-control min-h-40 fs-13"
                                       name="shipping_address_info[contact_person_name]"
                                       value="{{ $customer->f_name }} {{ $customer->l_name }}"
                                       placeholder="{{ translate('Full name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 fs-13">{{ translate('Phone') }}</label>
                                <input type="tel" class="form-control min-h-40 fs-13"
                                       name="shipping_address_info[phone]"
                                       value="{{ $customer->phone }}"
                                       placeholder="{{ translate('Phone number') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 fs-13">{{ translate('Country') }}</label>
                                <input type="text" class="form-control min-h-40 fs-13"
                                       name="shipping_address_info[country]"
                                       placeholder="{{ translate('Country') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 fs-13">{{ translate('City') }}</label>
                                <input type="text" class="form-control min-h-40 fs-13"
                                       name="shipping_address_info[city]"
                                       placeholder="{{ translate('City') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 fs-13">{{ translate('Zip Code') }}</label>
                                <input type="text" class="form-control min-h-40 fs-13"
                                       name="shipping_address_info[zip]"
                                       placeholder="{{ translate('Zip / Postal code') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label mb-1 fs-13">{{ translate('Address') }}</label>
                                <textarea class="form-control fs-13" rows="2"
                                          name="shipping_address_info[address]"
                                          placeholder="{{ translate('Street address, house number, etc.') }}"></textarea>
                            </div>
                            @if(getWebConfig('map_api_status') == 1)
                            <div class="col-12">
                                <input id="auction-pac-input"
                                       class="auction-map-search-input"
                                       type="text"
                                       placeholder="{{ translate('Search here') }}"/>
                                <div id="auction-shipping-map-canvas" style="height: 200px; border-radius: 4px; overflow: hidden;"></div>
                            </div>
                            @endif
                            <input type="hidden" id="auction-shipping-lat"
                                   name="shipping_address_info[latitude]"
                                   value="{{ $defaultLocation ? $defaultLocation['lat'] : 0 }}">
                            <input type="hidden" id="auction-shipping-lng"
                                   name="shipping_address_info[longitude]"
                                   value="{{ $defaultLocation ? $defaultLocation['lng'] : 0 }}">
                        </div>
                    </div>
                </div>

                <div class="bs-border shadow-sm box-light p-xxl-20px p-3 mb-3 rounded" id="billing-address-section">
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <h5 class="mb-0 fs-16 fw-semibold">{{ translate('Billing Address') }}</h5>
                        <div class="form-check d-flex align-items-center bg-white py-2 px-10 rounded border gap-2">
                            <label class="form-check-label fs-13 mb-0" for="sameAsBilling">
                                {{ translate('Same as Shipping Address') }}
                            </label>
                            <input class="form-check-input m-0 form-check-input_theme" type="checkbox"
                                   id="sameAsBilling" checked>
                        </div>
                    </div>
                    <div id="billing-fields" style="display: none;">
                        @if($shipping_addresses->isNotEmpty())
                        <div class="d-flex justify-content-end mt-3">
                            <select class="form-select outline-0 shadow-none w-auto py-2 bg-white bs-border fs-12" id="claim-saved-billing-address-select">
                                <option value="">{{ translate('Saved Address') }}</option>
                                @foreach($shipping_addresses as $addr)
                                    <option value="{{ json_encode($addr->only(['contact_person_name','phone','address','city','zip','country'])) }}">
                                        {{ ucfirst($addr->address_type) }}{{ $addr->address ? ' — '.\Illuminate\Support\Str::limit($addr->address, 28) : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label mb-1 fs-13">{{ translate('Contact Person Name') }}</label>
                                <input type="text" class="form-control min-h-40 fs-13"
                                       name="billing_address_info[contact_person_name]"
                                       placeholder="{{ translate('Full name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 fs-13">{{ translate('Phone') }}</label>
                                <input type="tel" class="form-control min-h-40 fs-13"
                                       name="billing_address_info[phone]"
                                       placeholder="{{ translate('Phone number') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 fs-13">{{ translate('Country') }}</label>
                                <input type="text" class="form-control min-h-40 fs-13"
                                       name="billing_address_info[country]"
                                       placeholder="{{ translate('Country') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 fs-13">{{ translate('City') }}</label>
                                <input type="text" class="form-control min-h-40 fs-13"
                                       name="billing_address_info[city]"
                                       placeholder="{{ translate('City') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1 fs-13">{{ translate('Zip Code') }}</label>
                                <input type="text" class="form-control min-h-40 fs-13"
                                       name="billing_address_info[zip]"
                                       placeholder="{{ translate('Zip / Postal code') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label mb-1 fs-13">{{ translate('Address') }}</label>
                                <textarea class="form-control fs-13" rows="2"
                                          name="billing_address_info[address]"
                                          placeholder="{{ translate('Street address, house number, etc.') }}"></textarea>
                            </div>
                            @if(getWebConfig('map_api_status') == 1)
                            <div class="col-12">
                                <input id="auction-pac-input-billing"
                                       class="auction-map-search-input"
                                       type="text"
                                       placeholder="{{ translate('Search here') }}"/>
                                <div id="auction-billing-map-canvas" style="height: 200px; border-radius: 4px; overflow: hidden;"></div>
                            </div>
                            @endif
                            <input type="hidden" id="auction-billing-lat"
                                   name="billing_address_info[latitude]"
                                   value="{{ $defaultLocation ? $defaultLocation['lat'] : 0 }}">
                            <input type="hidden" id="auction-billing-lng"
                                   name="billing_address_info[longitude]"
                                   value="{{ $defaultLocation ? $defaultLocation['lng'] : 0 }}">
                        </div>
                    </div>
                </div>
                @endif

            </div>

            <div class="col-lg-4">
                <div class="d-flex flex-column gap-4 position-sticky" style="top: 80px">
                    @if(!empty($isFinalizing))
                    <div class="p-15px card border-0 shadow-sm rounded">
                        <div class="bg-warning bg-opacity-10 rounded text-center py-12px px-3">
                            <h4 class="fs-16 fw-semibold text-warning mb-1">{{ translate('Auction ended — finalizing results') }}</h4>
                            <p class="fs-14 title-semidark mb-0">{{ translate('The winner is being determined. Please check back shortly.') }}</p>
                        </div>
                    </div>
                    @endif
                    @if(!empty($claimTimeLimitEnabled) && !empty($claimEndTime))
                    <div class="countdown-card p-15px card border-0 shadow-sm rounded">
                        <div class="light-box rounded p-xl-3 p-2">
                            <span class="cz-countdown flash-deal-countdown d-flex justify-content-center align-items-center gap-10px" data-countdown="{{ $claimEndTime }}">
                                <span class="cz-countdown-days">
                                    <span class="cz-countdown-value m-value d-center text-white fw-bold fs-16 rounded"></span>
                                    <span class="cz-countdown-text text-nowrap fs-10 fw-bold title-clr">{{ translate('days') }}</span>
                                </span>
                                <span class="cz-countdown-value pb-3 fs-14 text-primary fw-bold">:</span>
                                <span class="cz-countdown-hours">
                                    <span class="cz-countdown-value m-value d-center text-white fw-bold fs-16 rounded"></span>
                                    <span class="cz-countdown-text text-nowrap fs-10 fw-bold title-clr">{{ translate('hours') }}</span>
                                </span>
                                <span class="cz-countdown-value pb-3 fs-14 text-primary fw-bold">:</span>
                                <span class="cz-countdown-minutes">
                                    <span class="cz-countdown-value m-value d-center text-white fw-bold fs-16 rounded"></span>
                                    <span class="cz-countdown-text text-nowrap fs-10 fw-bold title-clr">{{ translate('minutes') }}</span>
                                </span>
                                <span class="cz-countdown-value pb-3 fs-14 text-primary fw-bold">:</span>
                                <span class="cz-countdown-seconds">
                                    <span class="cz-countdown-value m-value d-center text-white fw-bold fs-16 rounded"></span>
                                    <span class="cz-countdown-text text-nowrap fs-10 fw-bold title-clr">{{ translate('seconds') }}</span>
                                </span>
                            </span>
                            <p class="fs-13 title-semidark mb-0 text-center mt-2">
                                {{ translate('Claim your product before time runs out.') }}
                            </p>
                        </div>
                    </div>
                    @endif
                    <div class="p-xxl-20px p-3 card border-0 shadow-sm rounded">
                        <div class="mb-3 d-flex align-items-center justify-content-between gap-1">
                            <h4 class="fs-18 fw-semibold title-clr mb-0 line--limit-1">
                                {{ translate('Payment Method') }}
                            </h4>
                            <button type="button" id="claim-change-payment-btn"
                                    class="btn p-0 text-primary fs-16 outline-0 d-none"
                                    data-bs-toggle="modal" data-bs-target="#payment_method-choose">
                                {{ translate('Change') }}
                            </button>
                        </div>
                        <div id="claim-payment-display" class="d-flex flex-column gap-15px d-none mb-3">
                            <div id="claim-display-wallet-row" class="d-flex align-items-center gap-2 justify-content-between w-100 d-none">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="fs-14 title-semidark">{{ translate('Paid by Wallet') }}</div>
                                    <span class="title-semidark">:</span>
                                </div>
                                <div class="info-option2">
                                    <h4 class="fs-14 m-0 title-clr fw-semibold" id="claim-display-wallet-amount">-</h4>
                                </div>
                            </div>
                            <div id="claim-display-other-row" class="d-flex align-items-center gap-2 justify-content-between w-100 d-none">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="fs-14 title-semidark" id="claim-display-other-label">-</div>
                                    <span class="title-semidark">:</span>
                                </div>
                                <div class="info-option2">
                                    <h4 class="fs-14 m-0 title-clr fw-semibold" id="claim-display-other-amount">-</h4>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="claim-add-payment-btn"
                                class="btn outline-none light-box light-box-hover rounded cursor-pointer text-center fs-14 title-semidark p-3"
                                data-bs-toggle="modal" data-bs-target="#payment_method-choose">
                            <i class="fi fi-sr-add text-primary fs-20"></i>
                            {{ translate('Add Payment Method') }}
                        </button>
                    </div>
                    <div class="card bs-border shadow-sm rounded p-xxl-20px p-3">
                        <h5 class="fs-16 fw-semibold title-clr mb-3">{{ translate('Order Summary') }}</h5>

                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-14 title-semidark">{{ translate('Winning Bid') }}</span>
                                <span class="fs-14 fw-semibold title-clr">{{ webCurrencyConverter(amount: $bidAmount) }}</span>
                            </div>

                            @if($shippingFee > 0)
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-14 title-semidark">{{ translate('Shipping Fee') }}</span>
                                    <span class="fs-14 fw-semibold title-clr">{{ webCurrencyConverter(amount: $shippingFee) }}</span>
                                </div>
                            @endif

                            @if($systemTaxConfig['SystemTaxVat']['is_active'] && !$systemTaxConfig['is_included'] && $totalTaxAmount > 0)
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-14 title-semidark">{{ translate('tax') }}</span>
                                    <span class="fs-14 fw-semibold title-clr">
                                        {{ webCurrencyConverter(amount: $totalTaxAmount) }}
                                    </span>
                                </div>
                            @endif

                            <div class="border-bottom"></div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-16 fw-semibold title-clr">{{ translate('Total Payable') }}</span>
                                <span class="fs-16 fw-bold text-primary">{{ webCurrencyConverter(amount: $totalPayable) }}</span>
                            </div>

                            @if(!empty($auctionProduct->return_policy))
                                <div class="fs-12 py-2 px-2 badge text-wrap text-start bg-warning title-semidark fw-normal bg-opacity-10 d-flex align-items-center gap-1">
                                    <i class="fi fi-sr-info text-warning"></i>
                                    {{ translate('Return Policy') }}: {{ $auctionProduct->return_policy }}
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary w-100 mt-1 fs-15" {{ !empty($isFinalizing) ? 'disabled' : '' }}>
                                {{ translate('Proceed to Claim') }}
                            </button>

                            <a href="{{ route('auction.product-details', $auctionProduct->slug) }}"
                               class="btn btn-light border w-100 fs-14">
                                {{ translate('Cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="payment_method-choose" tabindex="-1" aria-labelledby="payment_method-chooseLebel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0 justify-content-between pe-4">
        <a class="d-flex align-items-center text-nowrap gap-1 text-capitalize text-decoration-none fs-14 view-all-text text-primary cursor-pointer" href="javascript:void(0)" data-bs-dismiss="modal">
            <i class="fi fi-rr-angle-left fs-12"></i>
            {{ translate('Go Back')}}
        </a>
        <h1 class="modal-title fs-18 fw-semibold title-clr text-center pe-4">
            {{ translate('Choose Payment Method') }}
        </h1>
        <div></div>
        <button type="button" class="position-absolute top-0 inline-end-0 m-2 btn p-0 text-light-gray bg-secondary rounded-circle d-center w-35px h-35px min-w-35px" data-bs-dismiss="modal" aria-label="Close">
            <i class="fi fi-rr-cross-small"></i>
        </button>
      </div>
      @php
          $claimWalletCoversAll = $wallet_balance >= $totalPayable;
          $claimWalletRemaining = max(0, $wallet_balance - $totalPayable);
          $claimWalletPaid      = min($wallet_balance, $totalPayable);
      @endphp
      <div class="modal-body pt-1 mx-sm-3">
        <div class="modal-body-scrolling">

            <div class="mb-20 text-center">
                <p class="fs-13 title-semidark mb-1">{{ translate('Total Payable') }}</p>
                <h3 class="fw-bold fs-20 mb-0">{{ webCurrencyConverter(amount: $totalPayable) }}</h3>
            </div>

            @if(auth('customer')->check() && $wallet_balance >= $totalPayable)
                <div class="border rounded p-3 d-flex align-items-center justify-content-between mb-20" id="claim-wallet-balance-section">
                    <div>
                        <p class="fs-10 title-semidark mb-1">{{ translate('Wallet Balance') }}</p>
                        <h3 class="fw-bold fs-18 mb-0">{{ webCurrencyConverter(amount: $wallet_balance) }}</h3>
                    </div>
                    <button type="button" id="claim-apply-wallet-btn" class="btn btn-outline-primary fw-semibold fs-12 py-2">
                        {{ translate('Apply') }}
                    </button>
                </div>

                <div class="border rounded p-3 d-flex align-items-center justify-content-between mb-20 d-none" id="claim-wallet-applied-section">
                    <div>
                        <p class="fs-10 title-semidark mb-1">{{ translate('Wallet Remaining Balance') }}</p>
                        <h3 class="fw-bold d-flex align-items-center gap-2px fs-18 mb-0">
                            {{ webCurrencyConverter(amount: $claimWalletRemaining) }}
                            <span class="text-primary fs-12 fw-normal">({{ translate('Applied') }})</span>
                        </h3>
                    </div>
                    <button type="button" id="claim-remove-wallet-btn" class="btn p-0 m-0 outline-0 fs-24 text-danger">
                        <i class="fi fi-rr-cross-small"></i>
                    </button>
                </div>

                <div class="mb-20 d-none" id="claim-paid-by-wallet-section">
                    <div class="border light-box rounded p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <label class="fw-bold fs-14 title-clr mb-0">{{ translate('Paid By Wallet') }}</label>
                        </div>
                        <h4 class="fs-20 fw-semibold title-clr mb-0">
                            {{ webCurrencyConverter(amount: $claimWalletPaid) }}
                        </h4>
                    </div>
                </div>
            @endif

            @if(isset($offline_payment) && $offline_payment['status'] && $offline_payment_methods->isNotEmpty())
                <div class="border rounded p-3 mb-20">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="d-flex align-items-center gap-3">
                            <input type="radio" id="claim-pay-offline-radio" name="claim_payment_choice"
                                   class="form-check-input form-check-input_theme" value="offline_payment">
                            <label for="claim-pay-offline-radio" class="fw-bold fs-14 title-clr mb-0 cursor-pointer">
                                {{ translate('Pay Offline') }}
                            </label>
                        </span>
                        <div data-toggle="tooltip" title="{{ translate('for_offline_payment_options,_please_follow_the_steps_below') }}">
                            <i class="fi fi-rr-info text-primary"></i>
                        </div>
                    </div>
                    <div class="mt-3 claim-offline-methods-card d-none">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($offline_payment_methods as $method)
                                <button type="button"
                                        class="btn btn-light claim-offline-method-btn text-capitalize"
                                        data-method-id="{{ $method->id }}"
                                        data-method-name="{{ $method->method_name }}"
                                        data-amount="{{ $totalPayable }}">
                                    {{ $method->method_name }}
                                </button>
                            @endforeach
                        </div>
                        <div id="claim-offline-method-field"></div>
                    </div>
                </div>
            @endif

            @if(isset($cash_on_delivery) && $cash_on_delivery['status'] == 1)
                <div class="border rounded p-3 mb-20">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="d-flex align-items-center gap-3">
                            <input type="radio" id="claim-pay-cod-radio" name="claim_payment_choice"
                                   class="form-check-input form-check-input_theme claim-cod-radio" value="cash_on_delivery">
                            <label for="claim-pay-cod-radio" class="fw-bold fs-14 title-clr mb-0 cursor-pointer">
                                {{ translate('Cash on Delivery') }}
                            </label>
                        </span>
                        <i class="fi fi-rr-truck-side fs-20 text-primary"></i>
                    </div>
                </div>
            @endif

            @if(isset($digital_payment) && $digital_payment['status'] == 1 && count($payment_gateways_list) > 0)
                <div class="border rounded p-3">
                    <div class="d-flex flex-column gap-xxl-4 gap-3">
                        <h6 class="fw-bold fs-14 title-clr mb-0">{{ translate('Pay Via Online') }}</h6>
                        @foreach($payment_gateways_list as $payment_gateway)
                            @php
                                $additionalData  = $payment_gateway['additional_data'] != null ? json_decode($payment_gateway['additional_data']) : null;
                                $gatewayImgPath  = dynamicAsset(path: 'public/assets/back-end/img/modal/payment-methods/' . $payment_gateway->key_name . '.png');
                                if ($additionalData?->gateway_image && file_exists(base_path('storage/app/public/payment_modules/gateway_image/' . $additionalData->gateway_image))) {
                                    $gatewayImgPath = dynamicStorage(path: 'storage/app/public/payment_modules/gateway_image/' . $additionalData->gateway_image);
                                }
                                $gatewayLabel = ($additionalData?->gateway_title) ? $additionalData->gateway_title : str_replace('_', ' ', $payment_gateway->key_name);
                            @endphp
                            <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                                <label class="d-flex align-items-center gap-1 fs-13 title-clr"
                                       for="claim_gw_{{ $loop->index }}">
                                    <img src="{{ $gatewayImgPath }}" alt="{{ $gatewayLabel }}" width="30">
                                    <span class="text-capitalize">{{ $gatewayLabel }}</span>
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input form-check-input_theme claim-gateway-radio"
                                           type="radio" name="claim_payment_choice"
                                           id="claim_gw_{{ $loop->index }}"
                                           value="{{ $payment_gateway->key_name }}"
                                           data-gateway-key="{{ $payment_gateway->key_name }}"
                                           data-gateway-label="{{ $gatewayLabel }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
      </div>
      <div class="modal-footer shadow-sm justify-content-center border-0">
        <button type="button" id="claim-payment-process-btn" class="btn btn-primary w-100" disabled>
            {{ translate('Process') }}
        </button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modal-claim-offline-payment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 justify-content-between pe-4">
                <a class="d-flex align-items-center text-nowrap gap-1 text-capitalize text-decoration-none fs-14 view-all-text text-primary cursor-pointer"
                   href="javascript:void(0)" id="claim-offline-go-back">
                    <i class="fi fi-rr-angle-left fs-12"></i>
                    {{ translate('Go Back') }}
                </a>
                <h1 class="modal-title fs-18 fw-semibold title-clr text-center">
                    {{ translate('Offline Payment') }}
                </h1>
                <div></div>
                <button type="button" class="position-absolute top-0 inline-end-0 m-2 btn p-0 text-light-gray bg-secondary rounded-circle d-center w-35px h-35px min-w-35px" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fi fi-rr-cross-small"></i>
                </button>
            </div>
            <div class="modal-body pt-1 mx-sm-3">
                <div id="claim-offline-payment-field"></div>
            </div>
            <div class="modal-footer justify-content-center border-0">
                <button type="button" id="claim-offline-confirm-btn" class="btn btn-primary w-100">
                    {{ translate('Confirm Payment') }}
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="participation_confirm" tabindex="-1" aria-labelledby="participation_confirmLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3 rounded-4">
      <div class="modal-header pt-0 border-0 justify-content-center">
        <button type="button" class="position-absolute top-0 inline-end-0 m-2 btn p-0 text-light-gray bg-secondary rounded-circle d-center w-35px h-35px min-w-35px" data-bs-dismiss="modal" aria-label="Close">
            <i class="fi fi-rr-cross-small"></i>
        </button>
      </div>
      <div class="modal-body">
        <div class="box text-center">
            <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/check-big.svg') }}" alt="" class="mb-20">
            <h3 class="fw-bold fs-16 title-clr mb-2">
                {{ translate('Participation Confirmed').'!' }}
            </h3>
            <p class="fs-14 pragraph-clr2 mb-0 px-sm-3 participation-confirmed-message">
                {!!  translate('You have successfully joined this auction.').' '.translate('You will be notified as soon as it goes live.') !!}
            </p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0">
        <div class="max-w-200px mx-auto w-100">
            <button type="button" class="w-100 btn btn-primary">
                {{ translate('View Details') }}
            </button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="claim-success-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3 rounded-4">
      <div class="modal-header pt-0 border-0 justify-content-center">
        <button type="button" class="position-absolute top-0 inline-end-0 m-2 btn p-0 text-light-gray bg-secondary rounded-circle d-center w-35px h-35px min-w-35px" data-bs-dismiss="modal" aria-label="Close">
            <i class="fi fi-rr-cross-small"></i>
        </button>
      </div>
      <div class="modal-body">
        <div class="box text-center">
            <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/check-big.svg') }}" alt="" class="mb-20">
            <h3 class="fw-bold fs-16 title-clr mb-2">
                {{ translate('Claimed Product Successfully') }}!
            </h3>
            <p class="fs-14 pragraph-clr2 mb-0 px-sm-3">
                {{ translate('Your product has been successfully claimed and is now being prepared for shipment.') }}
            </p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0">
        <div class="max-w-200px mx-auto w-100">
            <a href="{{ auth('customer')->check() && auth('customer')->id() == $auctionProduct?->owner_id && $auctionProduct?->owner_type == 'customer' ? route('auction.profile-view.author-product-details', ['slug' => $auctionProduct->slug]) : route('auction.profile-view.product-details', ['slug' => $auctionProduct->slug]) }}"
               class="w-100 btn btn-primary">
                {{ translate('Order Details') }}
            </a>
        </div>
      </div>
    </div>
  </div>
</div>

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form                  = document.getElementById('auction-claim-form');
        const sameAsBillingCheckbox = document.getElementById('sameAsBilling');
        const billingFields         = document.getElementById('billing-fields');

        if (sameAsBillingCheckbox && billingFields) {
            sameAsBillingCheckbox.addEventListener('change', function () {
                billingFields.style.display = this.checked ? 'none' : 'block';
            });
        }

        // --- Saved address: populate shipping fields on select, restore on deselect ---
        const savedAddressSelect = document.getElementById('claim-saved-address-select');
        if (savedAddressSelect && form) {
            const shippingFieldNames = ['contact_person_name', 'phone', 'address', 'city', 'zip', 'country'];
            const originalShippingValues = {};
            shippingFieldNames.forEach(function (fieldName) {
                const el = form.querySelector('[name="shipping_address_info[' + fieldName + ']"]');
                originalShippingValues[fieldName] = el ? el.value : '';
            });

            savedAddressSelect.addEventListener('change', function () {
                const setVal = function (fieldName, val) {
                    const el = form.querySelector('[name="shipping_address_info[' + fieldName + ']"]');
                    if (el) el.value = val !== undefined && val !== null ? val : '';
                };

                if (!this.value) {
                    shippingFieldNames.forEach(function (fieldName) {
                        setVal(fieldName, originalShippingValues[fieldName]);
                    });
                    return;
                }

                const addr = JSON.parse(this.value);
                setVal('contact_person_name', addr.contact_person_name);
                setVal('phone', addr.phone);
                setVal('address', addr.address);
                setVal('city', addr.city);
                setVal('zip', addr.zip);
                setVal('country', addr.country);
            });
        }

        // --- Saved billing address: populate billing fields on select ---
        const savedBillingAddressSelect = document.getElementById('claim-saved-billing-address-select');
        if (savedBillingAddressSelect && form) {
            const billingFieldNames = ['contact_person_name', 'phone', 'address', 'city', 'zip', 'country'];

            savedBillingAddressSelect.addEventListener('change', function () {
                const setVal = function (fieldName, val) {
                    const el = form.querySelector('[name="billing_address_info[' + fieldName + ']"]');
                    if (el) el.value = val !== undefined && val !== null ? val : '';
                };

                if (!this.value) {
                    billingFieldNames.forEach(function (fieldName) { setVal(fieldName, ''); });
                    return;
                }

                const addr = JSON.parse(this.value);
                setVal('contact_person_name', addr.contact_person_name);
                setVal('phone', addr.phone);
                setVal('address', addr.address);
                setVal('city', addr.city);
                setVal('zip', addr.zip);
                setVal('country', addr.country);
            });
        }

        function copyShippingToBilling() {
            const fields = ['contact_person_name', 'phone', 'country', 'city', 'zip', 'address'];
            fields.forEach(function (key) {
                const src = form.querySelector('[name="shipping_address_info[' + key + ']"]');
                const dst = form.querySelector('[name="billing_address_info[' + key + ']"]');
                if (src && dst) {
                    dst.value = src.value;
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const injectedPm = form.querySelector('input[type="hidden"][name="payment_method"][data-payment-injected]');
                if (!injectedPm) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ translate('please_select_a_payment_method') }}');
                    }
                    return;
                }

                if (sameAsBillingCheckbox && sameAsBillingCheckbox.checked && billingFields) {
                    copyShippingToBilling();
                }

                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                $.ajax({
                    url: form.action,
                    method: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (response) {
                        if (response.success) {
                            new bootstrap.Modal(document.getElementById('claim-success-modal')).show();
                        } else if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    },
                    error: function (xhr) {
                        if (submitBtn) submitBtn.disabled = false;
                        var msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : '{{ translate('Something went wrong. Please try again.') }}';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(msg);
                        }
                    }
                });
            });
        }

        // ===== Payment Method Modal Logic =====
        const claimPaymentModal    = document.getElementById('payment_method-choose');
        const claimOfflineModal    = document.getElementById('modal-claim-offline-payment');
        const claimAddBtn          = document.getElementById('claim-add-payment-btn');
        const claimChangeBtn       = document.getElementById('claim-change-payment-btn');
        const claimPaymentDisplay  = document.getElementById('claim-payment-display');
        const claimDisplayWalletRow    = document.getElementById('claim-display-wallet-row');
        const claimDisplayOtherRow     = document.getElementById('claim-display-other-row');
        const claimDisplayWalletAmount = document.getElementById('claim-display-wallet-amount');
        const claimDisplayOtherLabel   = document.getElementById('claim-display-other-label');
        const claimDisplayOtherAmount  = document.getElementById('claim-display-other-amount');
        const claimProcessBtn      = document.getElementById('claim-payment-process-btn');
        const applyWalletBtn       = document.getElementById('claim-apply-wallet-btn');
        const removeWalletBtn      = document.getElementById('claim-remove-wallet-btn');
        const walletBalanceSection = document.getElementById('claim-wallet-balance-section');
        const walletAppliedSection = document.getElementById('claim-wallet-applied-section');
        const paidByWalletSection  = document.getElementById('claim-paid-by-wallet-section');
        const offlineRadio         = document.getElementById('claim-pay-offline-radio');
        const offlineMethodsCard   = document.querySelector('.claim-offline-methods-card');
        const offlineGoBack        = document.getElementById('claim-offline-go-back');
        const offlineConfirmBtn    = document.getElementById('claim-offline-confirm-btn');

        const walletAmountDisplay  = '{{ webCurrencyConverter(amount: min($wallet_balance, $totalPayable)) }}';
        const totalPayableDisplay  = '{{ webCurrencyConverter(amount: $totalPayable) }}';

        let walletApplied             = false;
        let selectedOfflineMethodId   = null;
        let selectedOfflineMethodName = null;

        // --- Wallet: Apply ---
        if (applyWalletBtn) {
            applyWalletBtn.addEventListener('click', function () {
                walletApplied = true;
                walletBalanceSection.classList.add('d-none');
                walletAppliedSection.classList.remove('d-none');
                paidByWalletSection.classList.remove('d-none');
                if (claimProcessBtn) claimProcessBtn.disabled = false;
                // Single method: deselect any gateway/offline/COD radio and clear injected inputs
                document.querySelectorAll('.claim-gateway-radio, #claim-pay-offline-radio, .claim-cod-radio').forEach(function (r) { r.checked = false; });
                if (offlineMethodsCard) offlineMethodsCard.classList.add('d-none');
                form.querySelectorAll('[data-payment-injected]').forEach(function (el) { el.remove(); });
            });
        }

        // --- Wallet: Remove ---
        if (removeWalletBtn) {
            removeWalletBtn.addEventListener('click', function () {
                walletApplied = false;
                walletAppliedSection.classList.add('d-none');
                paidByWalletSection.classList.add('d-none');
                walletBalanceSection.classList.remove('d-none');
                const anyGateway = document.querySelector('.claim-gateway-radio:checked');
                if (claimProcessBtn) claimProcessBtn.disabled = !anyGateway;
            });
        }

        // --- Offline radio: show methods, disable Process (must pick a method via sub-modal) ---
        if (offlineRadio) {
            offlineRadio.addEventListener('change', function () {
                if (this.checked) {
                    if (walletApplied) {
                        walletApplied = false;
                        if (walletAppliedSection) walletAppliedSection.classList.add('d-none');
                        if (paidByWalletSection) paidByWalletSection.classList.add('d-none');
                        if (walletBalanceSection) walletBalanceSection.classList.remove('d-none');
                    }
                    if (offlineMethodsCard) offlineMethodsCard.classList.remove('d-none');
                    if (claimProcessBtn) claimProcessBtn.disabled = true;
                }
            });
        }

        // --- Gateway radio: clear wallet, hide offline methods, enable Process ---
        document.querySelectorAll('.claim-gateway-radio').forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (walletApplied) {
                    walletApplied = false;
                    if (walletAppliedSection) walletAppliedSection.classList.add('d-none');
                    if (paidByWalletSection) paidByWalletSection.classList.add('d-none');
                    if (walletBalanceSection) walletBalanceSection.classList.remove('d-none');
                }
                if (offlineMethodsCard) offlineMethodsCard.classList.add('d-none');
                if (claimProcessBtn) claimProcessBtn.disabled = false;
            });
        });

        // --- COD radio: clear wallet, enable Process ---
        document.querySelectorAll('.claim-cod-radio').forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (walletApplied) {
                    walletApplied = false;
                    if (walletAppliedSection) walletAppliedSection.classList.add('d-none');
                    if (paidByWalletSection) paidByWalletSection.classList.add('d-none');
                    if (walletBalanceSection) walletBalanceSection.classList.remove('d-none');
                }
                if (offlineMethodsCard) offlineMethodsCard.classList.add('d-none');
                if (claimProcessBtn) claimProcessBtn.disabled = false;
            });
        });

        // --- Offline method button: AJAX load fields → open offline sub-modal ---
        document.querySelectorAll('.claim-offline-method-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const methodId   = this.dataset.methodId;
                const methodName = this.dataset.methodName;
                const methodAmount = this.dataset.amount;
                const offlinePaymentField = document.getElementById('claim-offline-payment-field');
                const routeUrl = document.getElementById('route-pay-offline-method-list')?.dataset?.url;

                selectedOfflineMethodId   = methodId;
                selectedOfflineMethodName = methodName;

                if (offlinePaymentField) offlinePaymentField.innerHTML = '';

                if (routeUrl && methodId) {
                    $.ajax({
                        url: routeUrl + '?method_id=' + methodId + '&edit_due_amount=' + encodeURIComponent(methodAmount),
                        type: 'GET',
                        success: function (response) {
                            if (offlinePaymentField) offlinePaymentField.innerHTML = response?.methodHtml || '';

                            const bsPaymentModal = bootstrap.Modal.getInstance(claimPaymentModal);
                            if (bsPaymentModal) {
                                claimPaymentModal.addEventListener('hidden.bs.modal', function openOffline() {
                                    claimPaymentModal.removeEventListener('hidden.bs.modal', openOffline);
                                    new bootstrap.Modal(claimOfflineModal).show();
                                }, { once: true });
                                bsPaymentModal.hide();
                            }
                        },
                        error: function () {
                            if (typeof toastr !== 'undefined') {
                                toastr.error('{{ translate('Something went wrong. Please try again.') }}');
                            }
                        }
                    });
                }
            });
        });

        // --- Offline sub-modal: Go Back (reopen payment modal) ---
        if (offlineGoBack) {
            offlineGoBack.addEventListener('click', function (e) {
                e.preventDefault();
                const bsOfflineModal = bootstrap.Modal.getInstance(claimOfflineModal);
                if (bsOfflineModal) {
                    claimOfflineModal.addEventListener('hidden.bs.modal', function reopenPayment() {
                        claimOfflineModal.removeEventListener('hidden.bs.modal', reopenPayment);
                        new bootstrap.Modal(claimPaymentModal).show();
                    }, { once: true });
                    bsOfflineModal.hide();
                }
            });
        }

        // --- Offline sub-modal: Confirm Payment ---
        if (offlineConfirmBtn) {
            offlineConfirmBtn.addEventListener('click', function () {
                const offlineField = document.getElementById('claim-offline-payment-field');
                injectOfflineFieldsToMainForm(offlineField, selectedOfflineMethodId);
                showClaimPaymentSummary('offline', selectedOfflineMethodName, totalPayableDisplay);

                const bsOfflineModal = bootstrap.Modal.getInstance(claimOfflineModal);
                if (bsOfflineModal) bsOfflineModal.hide();
            });
        }

        // --- Modal Process button ---
        if (claimProcessBtn) {
            claimProcessBtn.addEventListener('click', function () {
                const selectedGateway = document.querySelector('.claim-gateway-radio:checked');
                const selectedCod     = document.querySelector('.claim-cod-radio:checked');
                const bsModal         = bootstrap.Modal.getInstance(claimPaymentModal);

                if (walletApplied && !selectedGateway && !selectedCod) {
                    clearInjectedPaymentInputs();
                    const hiddenWallet = document.createElement('input');
                    hiddenWallet.type  = 'hidden';
                    hiddenWallet.name  = 'payment_method';
                    hiddenWallet.value = 'wallet';
                    hiddenWallet.setAttribute('data-payment-injected', '1');
                    form.appendChild(hiddenWallet);
                    showClaimPaymentSummary('wallet', null, walletAmountDisplay);
                    if (bsModal) bsModal.hide();

                } else if (selectedGateway) {
                    const gatewayKey   = selectedGateway.dataset.gatewayKey;
                    const gatewayLabel = selectedGateway.dataset.gatewayLabel || gatewayKey;

                    clearInjectedPaymentInputs();
                    const hiddenPm = document.createElement('input');
                    hiddenPm.type  = 'hidden';
                    hiddenPm.name  = 'payment_method';
                    hiddenPm.value = gatewayKey;
                    hiddenPm.setAttribute('data-payment-injected', '1');
                    form.appendChild(hiddenPm);

                    showClaimPaymentSummary('gateway', gatewayLabel, totalPayableDisplay);
                    if (bsModal) bsModal.hide();

                } else if (selectedCod) {
                    clearInjectedPaymentInputs();
                    const hiddenCod = document.createElement('input');
                    hiddenCod.type  = 'hidden';
                    hiddenCod.name  = 'payment_method';
                    hiddenCod.value = 'cash_on_delivery';
                    hiddenCod.setAttribute('data-payment-injected', '1');
                    form.appendChild(hiddenCod);

                    showClaimPaymentSummary('cod', '{{ translate('Cash on Delivery') }}', totalPayableDisplay);
                    if (bsModal) bsModal.hide();
                }
            });
        }

        // --- Reset modal choice state when re-opened via "Change" ---
        if (claimPaymentModal) {
            claimPaymentModal.addEventListener('show.bs.modal', function () {
                document.querySelectorAll('.claim-gateway-radio, #claim-pay-offline-radio, .claim-cod-radio').forEach(function (r) {
                    r.checked = false;
                });
                if (offlineMethodsCard) offlineMethodsCard.classList.add('d-none');
                if (claimProcessBtn) claimProcessBtn.disabled = !walletApplied;
            });
        }

        // --- Helpers ---

        function showClaimPaymentSummary(type, label, amount) {
            if (!claimPaymentDisplay) return;

            claimPaymentDisplay.classList.remove('d-none');
            if (claimAddBtn) claimAddBtn.classList.add('d-none');
            if (claimChangeBtn) claimChangeBtn.classList.remove('d-none');

            if (claimDisplayWalletRow) claimDisplayWalletRow.classList.add('d-none');
            if (claimDisplayOtherRow) claimDisplayOtherRow.classList.add('d-none');

            if (type === 'wallet') {
                if (claimDisplayWalletRow) claimDisplayWalletRow.classList.remove('d-none');
                if (claimDisplayWalletAmount) claimDisplayWalletAmount.textContent = amount;
            } else {
                if (walletApplied && claimDisplayWalletRow) {
                    claimDisplayWalletRow.classList.remove('d-none');
                    if (claimDisplayWalletAmount) claimDisplayWalletAmount.textContent = walletAmountDisplay;
                }
                if (claimDisplayOtherRow) claimDisplayOtherRow.classList.remove('d-none');
                if (claimDisplayOtherLabel) claimDisplayOtherLabel.textContent = label;
                if (claimDisplayOtherAmount) claimDisplayOtherAmount.textContent = amount;
            }
        }

        function clearInjectedPaymentInputs() {
            if (!form) return;
            form.querySelectorAll('[data-payment-injected], [data-offline-injected]').forEach(function (el) { el.remove(); });
        }

        function injectOfflineFieldsToMainForm(sourceDiv, methodId) {
            if (!sourceDiv || !form) return;

            form.querySelectorAll('[data-offline-injected], [data-payment-injected]').forEach(function (el) { el.remove(); });

            const hiddenPm = document.createElement('input');
            hiddenPm.type  = 'hidden';
            hiddenPm.name  = 'payment_method';
            hiddenPm.value = 'offline_payment';
            hiddenPm.setAttribute('data-payment-injected', '1');
            form.appendChild(hiddenPm);

            sourceDiv.querySelectorAll('input:not([type="hidden"]), textarea').forEach(function (input) {
                if (!input.name || !input.value) return;
                const existing = form.querySelector('[name="' + CSS.escape(input.name) + '"]');
                if (existing && existing.tagName === 'TEXTAREA') {
                    existing.value = input.value;
                } else {
                    const hidden = document.createElement('input');
                    hidden.type  = 'hidden';
                    hidden.name  = input.name;
                    hidden.value = input.value;
                    hidden.setAttribute('data-offline-injected', '1');
                    form.appendChild(hidden);
                }
            });
        }
    });
</script>
<span id="auction-default-lat" data-value="{{ $defaultLocation ? $defaultLocation['lat'] : '-33.8688' }}" class="d-none"></span>
<span id="auction-default-lng" data-value="{{ $defaultLocation ? $defaultLocation['lng'] : '151.2195' }}" class="d-none"></span>

@if(getWebConfig('map_api_status') == 1)
    <style>
        .auction-map-search-input {
            top: 10px !important;
            outline: none;
            border: 1px solid #b1b1b1;
            padding: 5px 10px;
            height: 40px;
            font-size: 14px;
            width: auto;
        }
    </style>
    <script src="{{ dynamicAsset(path: 'public/assets/front-end/auction/js/claim-checkout-map.js') }}"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ getWebConfig('map_api_key') }}&callback=auctionMapsShopping&loading=async&libraries=places&v=3.56"
        defer>
    </script>
@endif
@endpush
@endsection
