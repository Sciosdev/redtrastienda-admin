@extends("auction.web-views.user-profile._profile-master")

@section('title', translate('Auction_List'))

@section('profile_content')
    <div>
        <?php $auctionTabs = $auctionTabs ?? collect(); ?>

        <div class="mb-20 d-flex align-items-center gap-1 flex-wrap justify-content-between">
            <h5 class="fs-18 fw-semibold title-clr text-capitalize mb-0">{{ translate('Auction List') }}</h5>
            @if(getWebConfig(name: 'active_auction_for_customer') == 1)
            <a href="{{ route('auction.acution-create-product') }}" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2 px-3">
                <i class="fi fi-sr-add"></i> {{ translate('Create Auction') }}
            </a>
            @endif
        </div>

        @if($hasAuctionProducts ?? false)
            <ul class="nav dark-support_scrollbar nav-rounded mb-3 text-nowrap flex-nowrap pb-2 d-flex overflow-auto align-items-center justify-content-start gap-xxl-20px gap-3" id="pills-tab" role="tablist">
                @foreach($auctionTabs as $tab)
                    <?php $isActive = $tab['key'] === $activeAuctionTab; ?>

                    <li class="nav-item" role="presentation">
                        <a
                            class="nav-link fs-14 {{ $isActive ? 'active' : '' }}"
                            id="{{ $tab['tab_id'] }}"
                            href="{{ route('auction.list', ['tab' => $tab['key']]) }}"
                            role="tab"
                            aria-controls="{{ $tab['pane_id'] }}"
                            aria-selected="{{ $isActive ? 'true' : 'false' }}"
                        >
                            {{ translate($tab['translation_key']) }} <span class="text-light-gray">({{ $tab['count'] }})</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content" id="pills-tabContent">
                @foreach($auctionTabs as $tab)
                    <?php $isActive = $tab['key'] === $activeAuctionTab; ?>

                    <div
                        class="tab-pane fade {{ $isActive ? 'show active' : '' }}"
                        id="{{ $tab['pane_id'] }}"
                        role="tabpanel"
                        aria-labelledby="{{ $tab['tab_id'] }}"
                        tabindex="0"
                    >
                        @if($tab['key'] === 'delivered')
                            {{-- TODO: Balance Unadjusted summary card — wire up real totals and Adjust Now logic before enabling --}}
                        @endif

                        <?php $tabProducts = $isActive ? ($auctionProducts ?? collect()) : collect(); ?>
                        @if($isActive && $tabProducts->isEmpty())
                            <div class="text-center bg-light rounded px-2 py-5">
                                <img width="50" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/auction-emptys.svg') }}" alt="" class="mb-3">
                                <p class="fs-14 title-semidark">{{ translate('No auction products found for this status.') }}</p>
                            </div>
                        @endif

                        <div class="row g-3">
                            @foreach($tabProducts as $auctionProduct)
                                <?php
                                    $isUpcoming   = $auctionProduct->auction_current_status === 'upcoming';
                                    $isLive       = $auctionProduct->auction_current_status === 'live';
                                    $timeLabel    = $isUpcoming ? translate('Start At') : translate('End At');
                                    $timeValue    = $isUpcoming ? $auctionProduct->start_time : $auctionProduct->end_time;
                                    $countdownEnd = $timeValue && $timeValue->isPast() ? now() : $timeValue;
                                    $cardDetailsUrl = auth('customer')->check() && auth('customer')->id() == $auctionProduct?->owner_id && $auctionProduct?->owner_type == 'customer'
                                        ? route('auction.profile-view.author-product-details', ['slug' => $auctionProduct->slug])
                                        : route('auction.profile-view.product-details', ['slug' => $auctionProduct->slug]);
                                ?>

                                <div class="col-md-6">
                                    <div class="item border rounded h-100">
                                        <div class="ending-soon-card bids-items-card bg-white card_dark_support border-0 rounded position-relative">
                                            <div class="d-flex align-items-start">
                                                <div class="rounded p-10px items-thumb overflow-hidden text-center position-relative">
                                                    <a href="{{ $cardDetailsUrl }}" class="m-thumbnail d-block">
                                                        <img src="{{ getStorageImages(path: $auctionProduct->thumbnail_full_url, type: 'product') }}" alt="{{ $auctionProduct->name }}" class="w-100 h-100">
                                                    </a>
                                                    @if($isUpcoming || $isLive)
                                                    <div class="countdown-time_box bottom-0 w-100 rounded-0 z-1 d-inline-flex bg-white shadow-sm flex-wrap gap-1 justify-content-between align-items-center py-5px px-2 lh-sm position-absolute">
                                                        <span class="fs-12 title-semidark text-nowrap">{{ $timeLabel }} :</span>
                                                        <div class="flex-aligns text-nowrap gap-1">
                                                            <div class="d-flex gap-2px countdown" data-end="{{ $countdownEnd ? $countdownEnd->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s') }}">
                                                                <div class="time-box flex-aligns gap-1px text-center fw-bold">
                                                                    <span class="time hours fw-bold title-clr fs-13">00</span>
                                                                    <div class="small title-clr fs-13">h</div>
                                                                </div>
                                                                <div class="time-box flex-aligns gap-1px text-center fw-bold">
                                                                    <span class="time minutes fw-bold title-clr fs-13">00</span>
                                                                    <div class="small title-clr fs-13">m</div>
                                                                </div>
                                                                <div class="time-box flex-aligns gap-1px text-center fw-bold">
                                                                    <span class="time seconds fw-bold title-clr fs-13">00</span>
                                                                    <div class="small title-clr fs-13">s</div>
                                                                </div>
                                                            </div>
                                                            <i class="fi fi-rr-time-oclock fs-12 text-danger"></i>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="w-100 py-10 px-10px pb-2 ps-0 d-flex flex-column flex-row justify-content-between">
                                                    <div>
                                                        <div class="fs-12 title-semidark mb-1 d-flex align-items-center flex-wrap gap-2">
                                                            <span>{{ translate('Auction ID') }}#{{ $auctionProduct->id }}</span>
                                                        </div>
                                                        <h6 class="mb-10px pe-xl-3 pe-1">
                                                            <a href="{{ $cardDetailsUrl }}" class="text-decoration-none lh-sm fs-14 text-capitalize fw-semibold title-clr line--limit-1 text-hover-primary">
                                                                {{ $auctionProduct->name }}
                                                            </a>
                                                        </h6>
                                                        <?php $hasHighestBid = ($auctionProduct->current_highest_bid_amount ?? 0) > 0; ?>
                                                        <div class="d-flex gap-10px justify-content-start mb-1 align-items-center">
                                                    <span class="fs-12 title-semidark">
                                                        {{ $hasHighestBid ? translate('Highest Bid') : translate('Start Price') }}
                                                    </span>
                                                            <strong class="{{ $hasHighestBid ? 'text-primary fw-semibold' : 'title-clr fw-medium' }} fs-14">
                                                                {{ webCurrencyConverter(amount: $hasHighestBid ? $auctionProduct->current_highest_bid_amount : $auctionProduct->starting_price) }}
                                                            </strong>
                                                        </div>
                                                        @if($tab['key'] === 'upcoming')
                                                        <div class="d-flex gap-10px justify-content-start mb-1 align-items-center">
                                                            <span class="fs-12 title-semidark">{{ translate('Entry Fee') }}</span>
                                                            <strong class="title-clr fs-14 fw-medium">{{ getAuctionProductEntryFee(price: $auctionProduct->starting_price, type: 'web', format: 'string') }}</strong>
                                                        </div>
                                                        @endif
                                                        <div class="d-flex gap-10px justify-content-start mb-1 align-items-center">
                                                            <span class="fs-12 title-semidark">{{ translate('Min Increment') }}</span>
                                                            <strong class="title-clr fs-14 fw-medium">
                                                                {{ webCurrencyConverter(amount: $auctionProduct->minimum_increment_amount) }}
                                                            </strong>
                                                        </div>
                                                        @if(!is_null($auctionProduct?->maximum_decrement_amount))
                                                        <div class="d-flex gap-10px justify-content-start mb-1 align-items-center">
                                                            <span class="fs-12 title-semidark">{{ translate('Max Decrement') }}</span>
                                                            <strong class="title-clr fs-14 fw-medium">
                                                                {{ webCurrencyConverter(amount: $auctionProduct->maximum_decrement_amount) }}
                                                            </strong>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-1">
                                                            <div class="d-flex gap-xxl-20px gap-2 small text-muted">
                                                        <span class="d-flex align-items-center gap-1 title-clr fs-12">
                                                            <i class="fi fi-rr-eye text-light-gray"></i>
                                                            {{ formatCompactNumber($auctionProduct->total_views) }}
                                                        </span>
                                                                @if(!$isUpcoming)
                                                                    <span class="d-flex align-items-center gap-1 title-clr fs-12">
                                                                <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/bid.svg') }}" alt="" class="svg">
                                                                {{ formatCompactNumber($auctionProduct->total_bids) }}
                                                            </span>
                                                                @endif
                                                                @if($tab['key'] === 'upcoming')
                                                                    <span class="d-flex align-items-center gap-1 title-clr fs-12">
                                                                        <i class="fi fi-rr-users text-light-gray"></i>
                                                                        {{ formatCompactNumber($auctionProduct->participants_count ?? 0) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            @if($tab['key'] !== 'delivered')
                                                            <div class="">
                                                        <span class="d-flex align-items-center gap-1 title-clr fs-14">
                                                            <span class="title-semidark fs-12">{{ translate('Participants') }}</span> {{ formatCompactNumber($auctionProduct->participants_count ?? 0) }}
                                                        </span>
                                                            </div>
                                                            @endif
                                                        </div>

                                                        @if($tab['key'] === 'delivered')
                                                            <?php
                                                                $claimTx             = $auctionProduct->transactions?->firstWhere('type', 'auction_payment');
                                                                // Only COD routes earnings to the owner (owner owes admin a commission). offline_payment / digital / wallet all settle through the platform, so the admin owes the owner -> withdraw flow. Mirrors the details page (_sidebar-claimed-for-author).
                                                                $isCod               = $claimTx?->payment_method === 'cash_on_delivery';
                                                                $breakdown           = $payoutBreakdowns[$auctionProduct->id] ?? null;
                                                                $detailsUrl          = auth('customer')->check() && auth('customer')->id() == $auctionProduct?->owner_id && $auctionProduct?->owner_type == 'customer' ? route('auction.profile-view.author-product-details', ['slug' => $auctionProduct->slug]) : route('auction.profile-view.product-details', ['slug' => $auctionProduct->slug]);
                                                                $withdrawReq         = $auctionProduct->latestCustomerWithdraw;
                                                                $commissionTx        = $auctionProduct->transactions?->firstWhere('type', 'commission_payment');
                                                                $commissionStatus    = $commissionTx?->payment_status;
                                                                $commissionIsPaid    = in_array($commissionStatus, [\Modules\Auction\app\Enums\PaymentStatus::PAID, \Modules\Auction\app\Enums\PaymentStatus::COMPLETED], true);
                                                                $commissionIsPending = $commissionStatus === \Modules\Auction\app\Enums\PaymentStatus::PENDING;
                                                            ?>

                                                            @if($isCod)
                                                                @if(!$commissionIsPaid)
                                                                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                                                        @if($auctionProduct->is_relaunched)
                                                                            <span class="badge bg-info text-white rounded-pill fs-11 px-2 py-1 d-inline-flex align-items-center gap-1">
                                                                                <i class="fi fi-rr-refresh"></i>{{ translate('Recreated') }}
                                                                            </span>
                                                                        @endif
                                                                        @if($commissionIsPending)
                                                                            <span class="d-flex align-items-center gap-1 title-semidark fs-12">
                                                                                <i class="fi fi-rr-time-fast text-warning"></i>
                                                                                {{ translate('Under Verification') }}
                                                                            </span>
                                                                        @else
                                                                            <span class="d-flex align-items-center gap-1 title-semidark fs-12">
                                                                                {{ translate('Amount to Pay Admin') }}:
                                                                                <strong class="text-danger fs-12">{{ webCurrencyConverter(amount: $breakdown?->commissionAmount ?? 0) }}</strong>
                                                                            </span>
                                                                            <button type="button" class="btn btn-primary py-1 px-2 fs-12 rounded-pill" style="cursor: default;">
                                                                                {{ translate('Pay Now') }}
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            @else
                                                                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                                                    @if($auctionProduct->is_relaunched)
                                                                        <span class="badge bg-info text-white rounded-pill fs-11 px-2 py-1 d-inline-flex align-items-center gap-1">
                                                                            <i class="fi fi-rr-refresh"></i>{{ translate('Recreated') }}
                                                                        </span>
                                                                    @endif
                                                                    @if($withdrawReq?->status === \Modules\Auction\app\Enums\WithdrawStatus::APPROVED)
                                                                        <span class="d-flex align-items-center gap-1 fs-12 text-success fw-semibold">
                                                                            <i class="fi fi-sr-check-circle"></i>
                                                                            {{ translate('Withdrawn') }}
                                                                        </span>
                                                                    @else
                                                                        <span class="d-flex flex-column gap-1 title-semidark fs-12">
                                                                            <span class="d-flex align-items-center gap-1">
                                                                                {{ translate('Withdraw Amount') }}:
                                                                                <strong class="text-success fs-12">{{ webCurrencyConverter(amount: $breakdown?->vendorReceivable ?? 0) }}</strong>
                                                                            </span>
                                                                            @if($withdrawReq?->status === \Modules\Auction\app\Enums\WithdrawStatus::REJECTED)
                                                                                <span class="text-danger fs-11">{{ translate('Previous request rejected') }}</span>
                                                                            @endif
                                                                        </span>
                                                                        <button type="button" class="btn btn-outline-primary py-1 px-2 fs-12 rounded-pill flex-shrink-0" style="cursor: default;">
                                                                            {{ translate('Withdraw Money') }}
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endif

                                                        @if($auctionProduct->is_relaunched && ($tab['key'] !== 'delivered' || (($isCod ?? false) && ($commissionIsPaid ?? false))))
                                                            <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                                                                <span class="badge bg-info text-white rounded-pill fs-11 px-2 py-1 d-inline-flex align-items-center gap-1">
                                                                    <i class="fi fi-rr-refresh"></i>{{ translate('Recreated') }}
                                                                </span>
                                                            </div>
                                                        @endif

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="position-absolute inline-end-0 top-0 m-xxl-2 m-xl-2 m-1">
                                                <div class="btn-group dropstart">
                                                    <button type="button" class="btn p-0 bg-transparent outline-0 border-0 shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fi fi-sr-menu-dots-vertical text-primary"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu2 shadow-sm px-2">
                                                        <li>
                                                            <a class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1" href="{{ auth('customer')->check() && auth('customer')->id() == $auctionProduct?->owner_id && $auctionProduct?->owner_type == 'customer' ? route('auction.profile-view.author-product-details', ['slug' => $auctionProduct->slug]) : route('auction.profile-view.product-details', ['slug' => $auctionProduct->slug]) }}">
                                                                {{ translate('View Details') }} <i class="fi fi-sr-eye text-primary"></i>
                                                            </a>
                                                        </li>
                                                        @if($isUpcoming)
                                                        <li>
                                                            <a class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1" href="{{ route('auction.auction-update-product', ['id' => $auctionProduct->id]) }}">
                                                                {{ translate('Edit Details') }} <i class="fi fi-sr-pen-circle text-primary"></i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button type="button"
                                                                    class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1 text-danger js-delete-auction-btn"
                                                                    data-auction-id="{{ $auctionProduct->id }}">
                                                                {{ translate('Delete Auction') }} <i class="fi fi-sr-trash text-danger"></i>
                                                            </button>
                                                        </li>
                                                        @endif
                                                        @if($isLive && ($auctionProduct->participants_count ?? 0) === 0)
                                                        <li>
                                                            <button type="button"
                                                                    class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1 text-danger js-cancel-auction-btn"
                                                                    data-auction-id="{{ $auctionProduct->id }}">
                                                                {{ translate('Cancel Auction') }} <i class="fi fi-sr-ban text-danger"></i>
                                                            </button>
                                                        </li>
                                                        @endif
                                                        @if($tab['key'] === 'unsold')
                                                        <li>
                                                            <a class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1"
                                                               href="{{ route('auction.auction-recreate-product', ['id' => $auctionProduct->id]) }}">
                                                                {{ translate('Recreate') }} <i class="fi fi-sr-rotate-right text-primary"></i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button type="button"
                                                                    class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1 text-danger js-delete-auction-btn"
                                                                    data-auction-id="{{ $auctionProduct->id }}">
                                                                {{ translate('Delete') }} <i class="fi fi-sr-trash text-danger"></i>
                                                            </button>
                                                        </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($isActive && isset($auctionProducts) && $auctionProducts->hasPages())
                            <div class="mt-4 d-flex justify-content-center">
                                {{ $auctionProducts->links() }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="card bs-border py-lg-5">
                <div class="card-body py-lg-5">
                    <div class="empty-content text-center py-5">
                        <div class="text-center mb-20">
                            <img width="50" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/bids-empty.png') }}" alt="">
                        </div>
                        <h4 class="fs-20 title-clr mb-lg-3 mb-2 fw-semibold">
                            {{ translate('Turn Your Product into an Auction') }}
                        </h4>
                        <p class="fs-14 title-semidark lh-24px mb-4 pb-lg-2 fw-normal">
                            {{ translate('Start an auction and sell your product at the best price.') }}
                        </p>
                        @if(getWebConfig(name: 'active_auction_for_customer') == 1)
                        <div class="text-center">
                            <a href="{{ route('auction.acution-create-product') }}" class="btn btn-primary mx-auto d-inline-flex align-items-center justify-content-center gap-2">
                                <i class="fi fi-sr-add"></i> {{ translate('Create Auction') }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection

@push('script')
    <form id="js-cancel-auction-form" method="POST" action="{{ route('auction.cancel') }}" class="d-none">
        @csrf
        <input type="hidden" name="auction_product_id" id="js-cancel-auction-product-id" value="">
    </form>
    <form id="js-delete-auction-form" method="POST" action="{{ route('auction.delete') }}" class="d-none">
        @csrf
        <input type="hidden" name="auction_product_id" id="js-delete-auction-product-id" value="">
    </form>
    <script>
        (function () {
            document.querySelectorAll('.js-cancel-auction-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const auctionId = this.dataset.auctionId;
                    Swal.fire({
                        title: '{{ translate('Cancel_Auction') }}',
                        text: '{{ translate('Are_you_sure_you_want_to_cancel_this_auction?_This_action_cannot_be_undone.') }}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '{{ translate('Yes,_Cancel_It') }}',
                        cancelButtonText: '{{ translate('No,_Keep_It') }}',
                        reverseButtons: true,
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            document.getElementById('js-cancel-auction-product-id').value = auctionId;
                            document.getElementById('js-cancel-auction-form').submit();
                        }
                    });
                });
            });
        })();
    </script>
    <script>
        (function () {
            document.querySelectorAll('.js-delete-auction-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const auctionId = this.dataset.auctionId;
                    Swal.fire({
                        title: '{{ translate('Delete_Auction') }}',
                        text: '{{ translate('This_will_permanently_delete_the_auction_and_all_associated_data._This_action_cannot_be_undone.') }}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '{{ translate('Yes,_Delete') }}',
                        cancelButtonText: '{{ translate('Cancel') }}',
                        reverseButtons: true,
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            document.getElementById('js-delete-auction-product-id').value = auctionId;
                            document.getElementById('js-delete-auction-form').submit();
                        }
                    });
                });
            });
        })();
    </script>
@endpush
