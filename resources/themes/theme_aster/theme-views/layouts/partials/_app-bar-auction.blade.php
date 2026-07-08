@php
    $isHomeActive    = Request::is('auction') || Request::is('auction/');
    $isSavedActive   = Request::is('auction/saved-products-list');
    $isBidsActive    = Request::is('auction/bids/list')
        || Request::is('auction/profile-view/product/*')
        || Request::is('auction/bids-product-details*');
    $isProfileActive = Request::is('user-profile') || Request::is('user-account*') || Request::is('account-*');

    $savedCount = auth('customer')->check()
        ? \Modules\Auction\app\Models\AuctionSavedProduct::where('user_id', auth('customer')->id())
            ->whereHas('auctionProduct', fn($q) => $q->active())
            ->count()
        : 0;
@endphp
<ul class="list-unstyled d-flex justify-content-around gap-3 mb-0 position-relative bg-white shadow-lg">
    <li>
        <a href="{{ route('auction.index') }}"
           class="d-flex align-items-center flex-column py-2 {{ $isHomeActive ? 'active' : '' }}">
            <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_app_bar_auction_home)">
                    <path d="M13 15.4932C11.6193 15.4932 10.5 16.6125 10.5 17.9932V22.9932H15.5V17.9932C15.5 16.6125 14.3807 15.4932 13 15.4932Z" fill="currentColor"/>
                    <path d="M17.1667 17.9934V22.9934H20.5C21.8807 22.9934 23 21.8741 23 20.4934V12.8926C23.0002 12.4597 22.832 12.0436 22.5308 11.7326L15.4492 4.07673C14.1996 2.72477 12.0907 2.64177 10.7388 3.8913C10.6746 3.95067 10.6127 4.01251 10.5534 4.07673L3.48418 11.7301C3.17395 12.0424 2.99988 12.4649 3 12.9051V20.4934C3 21.8741 4.1193 22.9934 5.5 22.9934H8.83332V17.9934C8.84891 15.7211 10.6836 13.8654 12.8987 13.812C15.1879 13.7568 17.1492 15.644 17.1667 17.9934Z" fill="#1B7FED"/>
                    <path d="M13 15.4932C11.6193 15.4932 10.5 16.6125 10.5 17.9932V22.9932H15.5V17.9932C15.5 16.6125 14.3807 15.4932 13 15.4932Z" fill="currentColor"/>
                </g>
                <defs>
                    <clipPath id="clip0_app_bar_auction_home">
                        <rect width="20" height="20" fill="white" transform="translate(3 3)"/>
                    </clipPath>
                </defs>
            </svg>
        </a>
    </li>
    <li>
        @if(auth('customer')->check())
            <a href="{{ route('auction.saved-products-list') }}"
               class="d-flex align-items-center flex-column py-2 {{ $isSavedActive ? 'active' : '' }}">
                <div class="position-relative">
                    <i class="bi bi-bookmark fs-20"></i>
                    <span class="count auction-saved-update-count top-0">{{ $savedCount }}</span>
                </div>
            </a>
        @else
            <a href="javascript:" class="d-flex align-items-center flex-column py-2" data-bs-toggle="modal"
               data-bs-target="#loginModal">
                <div class="position-relative">
                    <i class="bi bi-bookmark fs-20"></i>
                </div>
            </a>
        @endif
    </li>
    <li>
        @if(auth('customer')->check())
            <a href="{{ route('auction.bids.list') }}"
               class="d-flex align-items-center flex-column py-2 {{ $isBidsActive ? 'active' : '' }}">
                <div class="position-relative">
                    <i class="fi fi-sr-auction fs-20"></i>
                </div>
            </a>
        @else
            <a href="javascript:" class="d-flex align-items-center flex-column py-2" data-bs-toggle="modal"
               data-bs-target="#loginModal">
                <div class="position-relative">
                    <i class="fi fi-sr-auction fs-20"></i>
                </div>
            </a>
        @endif
    </li>
    <li>
        @if(auth('customer')->check())
            <a href="{{ route('user-profile') }}"
               class="d-flex align-items-center flex-column py-2 {{ $isProfileActive ? 'active' : '' }}">
                <div class="position-relative">
                    <i class="bi bi-person fs-20"></i>
                </div>
            </a>
        @else
            <a href="javascript:" class="d-flex align-items-center flex-column py-2" data-bs-toggle="modal"
               data-bs-target="#loginModal">
                <div class="position-relative">
                    <i class="bi bi-person fs-20"></i>
                </div>
            </a>
        @endif
    </li>
</ul>
