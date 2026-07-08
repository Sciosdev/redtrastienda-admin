@extends("auction.web-views.user-profile._profile-master")

@section('title', translate('Auctions Request List'))

<?php
use Modules\Auction\app\Enums\ApprovalStatus;

?>

@section('profile_content')
    <div>
        @php($approvalTabs = $approvalTabs ?? collect())
        @php($selectedTabKey = request('tab', $activeApprovalTab ?? 'pending'))
        @php($selectedTabData = $approvalTabs->firstWhere('key', $selectedTabKey) ?? $approvalTabs->first())
        @php($selectedTabLabel = data_get($selectedTabData, 'translation_key', 'pending'))
        @php($selectedTabCount = (int) data_get($selectedTabData, 'total_count', data_get($selectedTabData, 'count', 0)))
        @php($productLabel = $selectedTabCount > 1 ? translate('Products') : translate('Product'))

        <div class="mb-20 d-flex align-items-center gap-1 flex-wrap justify-content-between">
            <h5 class="fs-18 fw-semibold title-clr text-capitalize mb-0">
                {{ translate('Auctions Request List') }}
            </h5>
            @if (getWebConfig(name: 'active_auction_for_customer') == 1)
            <a href="{{ route('auction.acution-create-product') }}" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2 px-3">
                <i class="fi fi-sr-add"></i> {{ translate('Create Auction') }}
            </a>
            @endif
        </div>

        <ul class="nav nav-rounded mb-3 d-flex text-nowrap flex-nowrap pb-2 d-flex overflow-auto align-items-center justify-content-start gap-xxl-20px gap-3" id="pills-tab" role="tablist">
            @foreach($approvalTabs as $tab)
                @continue($tab['key'] === 'approved')
                @php($isActive = $tab['key'] === $activeApprovalTab)

                <li class="nav-item" role="presentation">
                    <a
                        class="nav-link fs-14 {{ $isActive ? 'active' : '' }}"
                        id="{{ $tab['tab_id'] }}"
                        href="{{ route('auction.auctions-request-list', ['tab' => $tab['key']]) }}"
                        role="tab"
                        aria-controls="{{ $tab['pane_id'] }}"
                        aria-selected="{{ $isActive ? 'true' : 'false' }}"
                    >
                        {{ translate($tab['translation_key']) }} <span class="text-light-gray">({{ $tab['total_count'] ?? $tab['count'] }})</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="pills-tabContent">
            @foreach($approvalTabs as $tab)
                @continue($tab['key'] === 'approved')
                @php($isActive = $tab['key'] === $activeApprovalTab)

                <div
                    class="tab-pane fade {{ $isActive ? 'show active' : '' }}"
                    id="{{ $tab['pane_id'] }}"
                    role="tabpanel"
                    aria-labelledby="{{ $tab['tab_id'] }}"
                    tabindex="0"
                >
                    <div class="row g-3">
                        @forelse($tab['products'] as $auctionProduct)
                            <div class="col-md-6">
                                <div class="item h-100">
                                    <div class="ending-soon-card bids-items-card bg-white bs-border rounded position-relative h-100">
                                        <div class="d-flex gap-10px align-items-center h-100">
                                            <div class="rounded items-thumb overflow-hidden text-center position-relative flex-shrink-0">
                                                <a href="{{ auth('customer')->check() && auth('customer')->id() == $auctionProduct?->owner_id && $auctionProduct?->owner_type == 'customer' ? route('auction.profile-view.author-product-details', ['slug' => $auctionProduct->slug]) : route('auction.profile-view.product-details', ['slug' => $auctionProduct->slug]) }}" class="m-thumbnail d-block">
                                                    <img
                                                        src="{{ getStorageImages(path: $auctionProduct->thumbnail_full_url, type: 'product') }}"
                                                        alt="{{ $auctionProduct->name }}"
                                                        class="w-100 h-100 object-fit-cover"
                                                    >
                                                </a>
                                            </div>
                                            <div class="w-100 py-2 pe-1">
                                                <div>
                                                    <div class="fs-12 title-semidark mb-1">
                                                        {{ translate('Auction ID') }}#{{ $auctionProduct->id }}
                                                    </div>
                                                    <h6 class="mb-10px pe-3">
                                                        <a
                                                            href="{{ auth('customer')->check() && auth('customer')->id() == $auctionProduct?->owner_id && $auctionProduct?->owner_type == 'customer' ? route('auction.profile-view.author-product-details', ['slug' => $auctionProduct->slug]) : route('auction.profile-view.product-details', ['slug' => $auctionProduct->slug]) }}"
                                                            class="text-decoration-none lh-sm fs-14 text-capitalize fw-semibold title-clr line--limit-1"
                                                        >
                                                            {{ $auctionProduct->name }}
                                                        </a>
                                                    </h6>
                                                    <div class="d-flex gap-10px justify-content-start mb-2 align-items-center">
                                                        <span class="fs-12 title-semidark">{{ translate('Start Price') }}</span>
                                                        <strong class="text-primary fs-14 fw-semibold">
                                                            {{ webCurrencyConverter(amount: $auctionProduct->starting_price) }}
                                                        </strong>
                                                    </div>
                                                    <div class="d-flex gap-10px justify-content-start mb-2 align-items-center">
                                                        <span class="fs-12 title-semidark">{{ translate('Min Increment') }}</span>
                                                        <strong class="title-clr fs-14 fw-semibold">
                                                            {{ webCurrencyConverter(amount: $auctionProduct->minimum_increment_amount) }}
                                                        </strong>
                                                    </div>
                                                    @if(!is_null($auctionProduct?->maximum_decrement_amount))
                                                    <div class="d-flex gap-10px justify-content-start mb-2 align-items-center">
                                                        <span class="fs-12 title-semidark">{{ translate('Max Decrement') }}</span>
                                                        <strong class="title-clr fs-14 fw-semibold">
                                                            {{ webCurrencyConverter(amount: $auctionProduct->maximum_decrement_amount) }}
                                                        </strong>
                                                    </div>
                                                    @endif
                                                    <div class="d-flex gap-10px justify-content-start mb-0 align-items-center">
                                                        <span class="fs-12 title-semidark">{{ translate('Category') }}</span>
                                                        <strong class="title-clr fs-14 fw-semibold">
                                                            {{ $auctionProduct?->category?->name ?? translate('N/A') }}
                                                        </strong>
                                                    </div>
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
                                                        <a
                                                            class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1"
                                                            href="{{ auth('customer')->check() && auth('customer')->id() == $auctionProduct?->owner_id && $auctionProduct?->owner_type == 'customer' ? route('auction.profile-view.author-product-details', ['slug' => $auctionProduct->slug]) : route('auction.profile-view.product-details', ['slug' => $auctionProduct->slug]) }}"
                                                        >
                                                            {{ translate('View Details') }} <i class="fi fi-sr-eye text-primary"></i>
                                                        </a>
                                                    </li>
                                                    @if(in_array($tab['key'], [ApprovalStatus::PENDING, ApprovalStatus::REJECTED, \Modules\Auction\app\Enums\AuctionStatus::CANCELED]))
                                                    <li>
                                                        <a
                                                            class="dropdown-item py-2 d-flex align-items-center justify-content-between gap-1"
                                                            href="{{ route('auction.auction-update-product', ['id' => $auctionProduct->id]) }}"
                                                        >
                                                            {{ translate('Edit Details') }} <i class="fi fi-sr-pen-circle text-primary"></i>
                                                        </a>
                                                    </li>
                                                    @endif
                                                    @if(in_array($tab['key'], [\Modules\Auction\app\Enums\ApprovalStatus::PENDING, \Modules\Auction\app\Enums\ApprovalStatus::REJECTED, \Modules\Auction\app\Enums\AuctionStatus::CANCELED]))
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
                        @empty
                            <div class="bg-light py-5 px-2 rounded text-center">
                                <div>
                                    <img width="55" height="55" src="{{ dynamicAsset(path: 'public/assets/front-end/img/empty-icons/auction-emptys.svg') }}" alt="" class="mb-3">
                                    <div class="m-0 fs-14 title-semidark">{{ translate('No Data') }}</div>
                                </div>
                            </div>
                        @endforelse

                        @if($tab['products']->hasPages())
                            <div class="col-12">
                                <div class="card bs-border border-0 shadow-sm rounded-3 mt-2">
                                    <div class="card-body p-3">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="fs-13 title-semidark">
                                                {{ translate('Showing') }} {{ $tab['products']->count() }} {{ translate('of') }} {{ $tab['total_count'] ?? $tab['count'] }} {{ translate('auction_items') }}
                                            </div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                                                @if($tab['products']->previousPageUrl())
                                                    <a
                                                        href="{{ $tab['products']->appends(['tab' => $tab['key']])->previousPageUrl() }}"
                                                        class="btn btn-outline-primary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 fs-13"
                                                    >
                                                        <i class="fi fi-rr-angle-small-left"></i>
                                                        {{ translate('Previous') }}
                                                    </a>
                                                @else
                                                    <span class="btn btn-outline-secondary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 fs-13 disabled pe-none opacity-50">
                                                <i class="fi fi-rr-angle-small-left"></i>
                                                {{ translate('Previous') }}
                                            </span>
                                                @endif

                                                @if($tab['products']->nextPageUrl())
                                                    <a
                                                        href="{{ $tab['products']->appends(['tab' => $tab['key']])->nextPageUrl() }}"
                                                        class="btn btn-primary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 fs-13"
                                                    >
                                                        {{ translate('Next') }}
                                                        <i class="fi fi-rr-angle-small-right"></i>
                                                    </a>
                                                @else
                                                    <span class="btn btn-outline-secondary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 fs-13 disabled pe-none opacity-50">
                                                {{ translate('Next') }}
                                                <i class="fi fi-rr-angle-small-right"></i>
                                            </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>
@endsection

@push('script')
    <form id="js-delete-auction-form" method="POST" action="{{ route('auction.delete') }}" class="d-none">
        @csrf
        <input type="hidden" name="auction_product_id" id="js-delete-auction-product-id" value="">
    </form>
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
