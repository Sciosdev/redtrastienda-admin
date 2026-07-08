@php
    use function App\Utils\customer_info;
    $customer = customer_info();
    $customerId   = $customer?->id ?? 0;
    $firstName    = $customer?->f_name ?? 'Guest';
    $lastName     = $customer?->l_name ?? 'User';
    $createdAt    = $customer?->created_at ? date('d M, Y', strtotime($customer->created_at)) : 'N/A';
    $isAuctionMenuActive = request()->routeIs(
        'auction.list',
        'auction.auctions-request-list',
        'auction.acution-create-product',
        'auction.auction-recreate-product',
        'auction.auction-update-product',
        'auction.profile-view.author-product-details',
    );
    $isAuctionListActive = request()->routeIs('auction.list')
        || (request()->routeIs('auction.profile-view.author-product-details') && empty($isAuctionInactiveState));
    $isMyBidsActive = request()->routeIs(
        'auction.bids.list',
        'auction.profile-view.product-details',
        'auction.bids-product-details',
        'auction.bids-product-details-after-place-order',
    );
    $isSavedAuctionActive = request()->routeIs('auction.saved-products-list');
    $isSalesReportActive = request()->routeIs('auction.sales-report');
    $isTransactionHistoryActive = request()->routeIs('auction.transaction-history');
@endphp
<div class="col-lg-3">
    <div class="card profile-sidebar-sticky">
        <div class="card-body position-relative">
            <div class="d-none d-lg-flex justify-content-end">
                <div class="dropdown">
                    <button class="btn-circle p-0 bg-primary absolute-white size-1-125rem" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots fs-10 grid-center"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:"
                               class="delete-action"
                               data-action="{{ $customerId ? route('account-delete',[$customerId]) : '' }}"
                               data-message="{{translate('want_to_delete_this_account').' ?'}}">
                                {{ translate('delete_my_account') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div
                class="d-lg-none bg-primary rounded px-1 text-white cursor-pointer mt-3 position-absolute py-1 inset-inline-end-0 inline-end-0 mx-3 top-1 profile-menu-toggle">
                <i class="bi bi-list fs-18 absolute-text-white lh-1"></i>
            </div>
            <div class="d-flex flex-row flex-lg-column gap-2 gap-lg-4 align-items-center pb-2">
                <div class="avatar overflow-hidden profile-sidebar-avatar border border-primary rounded-circle d-flex">
                    <img class="img-fit dark-support"
                         src="{{ getStorageImages(path: $customer?->image_full_url, type:'avatar') }}" alt="">
                </div>

                <div class="text-lg-center">
                    <h5 class="mb-1">{{$firstName}} {{$lastName}}</h5>
                    <p class="fw-medium">{{translate('joined')}} {{ $createdAt}}</p>
                </div>
            </div>
            <div class="profile-menu-aside">
                <div class="profile-menu-aside-close d-lg-none d-flex">
                    <i class="bi bi-x-lg text-primary"></i>
                </div>
                <ul class="list-unstyled profile-menu gap-1 mt-3">
                    <li class="{{Request::is('user-profile') || Request::is('user-account') ||Request::is('account-address-*') ? 'active' :''}}">
                        <a href="{{ route('user-profile') }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon.png') }}"
                                 class="dark-support" alt="">
                            <span class="text-capitalize">{{translate('my_profile')}}</span>
                        </a>
                    </li>
                    <li class="{{Request::is('account-oder*') || Request::is('account-order-details*') || Request::is('refund-details*') || Request::is('track-order/order-wise-result-view') ? 'active' :''}}">
                        <a href="{{route('account-oder')}}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon2.png') }}"
                                 class="dark-support" alt="">
                            <span>{{translate('Orders')}}</span>
                        </a>
                    </li>
                    <li class="{{Request::is('user-restock-requests*') ? 'active' :''}}">
                        <a href="{{ route('user-restock-requests') }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/restock-request.png') }}"
                                 class="dark-support" alt="">
                            <span>{{translate('Restock_Requests')}}</span>
                        </a>
                    </li>
                    <li class="{{Request::is('wishlists') ? 'active' :''}}">
                        <a href="{{route('wishlists')}}">
                            <img width="20" src="{{theme_asset('assets/img/icons/profile-icon3.png')}}"
                                 class="dark-support" alt="">
                            <span>{{translate('Wish_List')}}</span>
                        </a>
                    </li>
                    <li class="{{Request::is('product-compare/index') ? 'active' :''}}">
                        <a href="{{route('product-compare.index')}}">
                            <img width="20" src="{{theme_asset('assets/img/icons/profile-icon4.png')}}"
                                 class="dark-support" alt="">
                            <span>{{translate('Compare_List')}}</span>
                        </a>
                    </li>

                    @if ($web_config['wallet_status'] == 1)
                        <li class="{{Request::is('wallet') ? 'active' :''}}">
                            <a href="{{route('wallet')}}">
                                <img width="20" src="{{theme_asset('assets/img/icons/profile-icon5.png')}}"
                                     class="dark-support" alt="">
                                <span>{{translate('wallet')}}</span>
                            </a>
                        </li>
                    @endif
                    @if ($web_config['loyalty_point_status'] == 1)
                        <li class="{{Request::is ('loyalty') ? 'active' : ''}}">
                            <a href="{{route('loyalty')}}">
                                <img width="20" src="{{theme_asset('assets/img/icons/profile-icon6.png')}}"
                                     class="dark-support" alt="">
                                <span class="text-capitalize">{{translate('loyalty_point')}}</span>
                            </a>
                        </li>
                    @endif
                    <li class="{{Request::is ('chat/vendor') || Request::is ('chat/delivery-man') ? 'active' : ''}}">
                        <a href="{{route('chat', ['type' => 'vendor'])}}">
                            <img width="20" src="{{theme_asset('assets/img/icons/profile-icon7.png')}}"
                                 class="dark-support" alt="">
                            <span>{{translate('inbox')}}</span>
                        </a>
                    </li>
                    <li class="{{Request::is ('account-tickets') || Request::is('support-ticket*') ? 'active' : ''}}">
                        <a href="{{route('account-tickets')}}">
                            <img width="20" src="{{theme_asset('assets/img/icons/profile-icon8.png')}}"
                                 class="dark-support" alt="">
                            <span class="text-capitalize">{{translate('support_ticket')}}</span>
                        </a>
                    </li>
                    @if ($web_config['ref_earning_status'])
                        <li class="{{Request::is ('refer-earn') || Request::is('refer-earn*') ? 'active' : ''}}">
                            <a href="{{ route('refer-earn') }}">
                                <img width="20" src="{{theme_asset('assets/img/icons/refer-and-earn.svg')}}"
                                     class="dark-support" alt="">
                                <span>{{translate('refer_&_earn')}}</span>
                            </a>
                        </li>
                    @endif
                    <li class="{{Request::is ('user-coupons') || Request::is('user-coupons*') ? 'active' : ''}}">
                        <a href="{{route('user-coupons')}}">
                            <img width="20" src="{{theme_asset('assets/img/icons/coupon.svg')}}" class="dark-support"
                                 alt="">
                            <span>{{translate('coupons')}}</span>
                        </a>
                    </li>
                    <li class="d-lg-none">
                      <a href="javascript:"
                               class="delete-action"
                               data-action="{{ $customerId ? route('account-delete',[$customerId]) : '' }}"
                               data-message="{{translate('want_to_delete_this_account').' ?'}}">
                                {{ translate('delete_my_account') }}
                            </a>
                    </li>
                    <li class="w-100">
                        @if(function_exists('shouldShowAuctionMenu') && shouldShowAuctionMenu())
                            <div class="light-box bg--light rounded p-10px mt-3">
                                <h6 class="fs-14 mb-10px title-clr fw-normal">{{ translate('Auction') }}</h6>
                                <div class="d-flex flex-column gap-10px">
                                    <a href="{{ route('auction.bids.list') }}" class="aside-items-profile aside-items-auction rounded-1 py-2 px-2 d-flex align-items-center gap-10px text-light-gray fs-14 fw-normal {{ $isMyBidsActive ? 'active' : '' }}">
                                        <img width="20" height="20" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/my-bids.svg') }}" alt="" class="w-20px h-20px object-contain svg">
                                        {{ translate('My_Bids') }}
                                    </a>
                                    <div class="menu--caret-accordion {{ $isAuctionMenuActive ? 'open' : '' }}">
                                        <div class="card-header cursor-pointer menu--caret aside-items-profile aside-items-auction {{ $isAuctionMenuActive ? 'active' : '' }} rounded menu--caret-box p-10px d-flex align-items-center gap-1 justify-content-between sub-header">
                                            <a href="javascript:void(0)" class="names cursor--pointer d-flex gap-10px align-items-center title-semidark">
                                                <img width="20" height="20" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/my-auctions.svg') }}" alt="" class="w-20px h-20px svg">
                                                <span class="line--limit-1 fs-14 fw-normal">
                                                    {{ translate('My_Auctions') }}
                                                </span>
                                            </a>
                                            <div class="cursor--pointer arrow_rot_icon">
                                                <strong><i class="fi fi-rr-angle-right fs-12 title-clr"></i></strong>
                                            </div>
                                        </div>
                                        <div class="card-body bg-light p-0 pb-2 ps-2 bg-white {{ $isAuctionMenuActive ? '' : 'd--none' }}">
                                            @if(getWebConfig(name: 'auction_feature_status') && getWebConfig(name: 'active_auction_for_customer') == 1)
                                                <a href="{{ route('auction.acution-create-product') }}" class="py-2 fs-13 title-clr d-flex align-items-center gap-1 acount-items {{ request()->routeIs('auction.acution-create-product', 'auction.auction-recreate-product', 'auction.auction-update-product') ? 'active' : '' }}">
                                                    <i class="fi fi-sr-wifi-1 fs-14"></i> {{ translate('Create_Auction') }}
                                                </a>
                                            @endif
                                            <a href="{{ route('auction.list') }}" class="py-2 fs-13 title-clr d-flex align-items-center gap-1 acount-items {{ $isAuctionListActive ? 'active' : '' }}">
                                                <i class="fi fi-sr-wifi-1 fs-14"></i> {{ translate('Auction_List') }}
                                            </a>
                                            <a href="{{ route('auction.auctions-request-list') }}" class="py-2 fs-13 title-clr d-flex align-items-center gap-1 acount-items {{ request()->routeIs('auction.auctions-request-list', 'auction.auction-update-product', 'auction.auction-recreate-product') || (request()->routeIs('auction.profile-view.author-product-details') && !empty($isAuctionInactiveState)) ? 'active' : '' }}">
                                                <i class="fi fi-sr-wifi-1 fs-14"></i> {{ translate('Auctions Request List') }}
                                            </a>
                                        </div>
                                    </div>
                                    <a href="{{ route('auction.saved-products-list') }}" class="aside-items-profile aside-items-auction rounded-1 py-2 px-2 d-flex align-items-center gap-10px text-light-gray fs-14 fw-normal {{ $isSavedAuctionActive ? 'active' : '' }}">
                                        <img width="20" height="20" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/badge.svg') }}" alt="" class="w-20px h-20px object-contain svg">
                                        {{ translate('Saved_Auction') }}
                                    </a>
                                    <a href="{{ route('auction.sales-report') }}" class="aside-items-profile aside-items-auction rounded-1 py-2 px-2 d-flex align-items-center gap-10px text-light-gray fs-14 fw-normal {{ $isSalesReportActive ? 'active' : '' }}">
                                        <img width="20" height="20" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/display-chartup.svg') }}" alt="" class="w-20px h-20px object-contain svg">
                                        {{ translate('Auction_Sales_Report') }}
                                    </a>
                                    <a href="{{ route('auction.transaction-history') }}" class="aside-items-profile aside-items-auction rounded-1 py-2 px-2 d-flex align-items-center gap-10px text-light-gray fs-14 fw-normal {{ $isTransactionHistoryActive ? 'active' : '' }}">
                                        <i class="fi fi-rr-list fs-14"></i>
                                        {{ translate('Transaction_with_Admin') }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </li>
                </ul>
            </div><div class="aside-overlay"></div>
        </div>
    </div>
</div>
