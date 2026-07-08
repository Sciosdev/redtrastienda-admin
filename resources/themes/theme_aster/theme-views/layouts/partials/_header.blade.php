@php
    use App\Models\Brand;
    use App\Models\Category;
    use App\Utils\Helpers;
    $categories = \App\Utils\CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting(dataLimit: 11);
    $brands = \App\Utils\BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();
@endphp

@if (isset($web_config['announcement']) && $web_config['announcement']['status']==1)
    <div class="offer-bar py-2 py-sm-3 announcement-color d--none">
        <div class="d-flex gap-2 align-items-center">
            <div class="offer-bar-close">
                <i class="bi bi-x-lg"></i>
            </div>
            <div class="top-offer-text flex-grow-1 d-flex justify-content-center fw-semibold ">
                {{ $web_config['announcement']['announcement'] }}
            </div>
        </div>
    </div>
@endif
<header class="header">
    <div class="header-top py-2">
        <div class="container">
            <div class="d-flex align-items-center flex-wrap justify-content-between gap-2">
                <a href="tel:+{{ $web_config['phone'] }}" class="d-flex gap-2 align-items-center direction-ltr">
                    <i class="bi bi-telephone text-primary"></i>
                    {{ $web_config['phone'] }}
                </a>

                <ul class="nav justify-content-center justify-content-sm-end align-items-center gap-4">
                    <li>
                        <div class="language-dropdown">
                            @if($web_config['currency_model']=='multi_currency')
                                <button
                                    type="button"
                                    class="border-0 bg-transparent d-flex gap-2 align-items-center dropdown-toggle text-dark p-0"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    {{ session('currency_code') }} {{ session('currency_symbol') }}
                                </button>
                                <ul class="dropdown-menu bs-dropdown-min-width--10rem">
                                    @foreach ($web_config['currencies'] as $key => $currency)
                                        <li class="currency-change" data-currency-code="{{$currency['code']}}">
                                            <a href="javascript:">{{ $currency->name }}</a>
                                        </li>
                                    @endforeach
                                    <span id="currency-route" data-currency-route="{{ route('currency.change') }}"></span>
                                </ul>
                            @endif
                        </div>
                    </li>
                    <li>
                        <div class="language-dropdown">
                            <button
                                type="button"
                                class="border-0 bg-transparent d-flex gap-2 align-items-center dropdown-toggle text-dark p-0"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                @php( $local = Helpers::default_lang())
                                @foreach($web_config['language'] as $data)
                                    @if($data['code']==$local)
                                        <?php
                                            if (\Illuminate\Support\Str::contains($data['code'], '-')) {
                                                $countryCodeArr = explode('-', $data['code']);
                                                $data['code'] = $countryCodeArr[0];
                                            }
                                        ?>
                                        <img width="20"
                                             src="{{ theme_asset('assets/img/flags/' . strtolower($data['code']).'.png') }}"
                                             class="dark-support" alt="{{ translate('Eng') }}"/>
                                        {{ ucwords($data['name']) }}
                                    @endif
                                @endforeach
                            </button>
                            <ul class="dropdown-menu bs-dropdown-min-width--10rem">
                                @foreach($web_config['language'] as $key =>$data)
                                    @if($data['status']==1)
                                        <?php
                                            if (\Illuminate\Support\Str::contains($data['code'], '-')) {
                                                $countryCodeArr = explode('-', $data['code']);
                                                $data['code'] = $countryCodeArr[0];
                                            }
                                        ?>
                                        <li class="change-language" data-action="{{ route('change-language') }}" data-language-code="{{$data['code']}}">
                                            <a class="d-flex gap-2 align-items-center" href="javascript:">
                                                <img width="20" src="{{ theme_asset('assets/img/flags/' . strtolower($data['code']).'.png') }}"
                                                     loading="lazy" class="dark-support" alt="{{$data['name']}}"/>
                                                {{ ucwords($data['name']) }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </li>
                    @if($web_config['business_mode'] == 'multi' && $web_config['seller_registration'])
                        <li class="d-none d-xl-block">
                            <a href="{{ route('vendor.auth.registration.index') }}" class="d-flex">
                                <div class="fz-16 text-capitalize">{{ translate('become_a_vendor') }}</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <div class="header-middle border-bottom py-2 d-none d-xl-block">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div class="logo-area d-flex align-items-center gap-2">
                    <a class="logo system-logo-element" href="{{ (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? route('auction.index') : route('home') }}">
                        <img class="dark-support svg h-45" alt="{{ translate('Logo') }}"
                            src="{{ getStorageImages(path: $web_config['web_logo'], type:'logo') }}">
                    </a>

                    @if(getWebConfig(name: 'auction_feature_status') && Request::is('auction*'))
                        <span class="badge text-white bg-warning-dark rounded-pill fs-14 fw-semibold mx-1">{{ translate('Auction') }}</span>
                    @endif
                </div>
                <div class="search-box position-relative">
                    <form action="{{ (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? route('auction.products') : route('products') }}" type="submit">
                        <div class="d-flex">
                            <div class="select-wrap focus-border border border-end-logical-0 d-flex align-items-center">
                                <div class="border-end">
                                    <?php
                                        $requestCategoryIds = request('category_ids');
                                        $requestAllCategoryList = isset($requestCategoryIds[0]) && $requestCategoryIds[0] == 'all';
                                    ?>
                                    <div class="dropdown search_dropdown">
                                        <button type="button"
                                                class="border-0 px-3 bg-transparent dropdown-toggle text-dark py-0 text-capitalize header-search-dropdown-button"
                                                data-bs-toggle="dropdown" aria-expanded="false" data-default="{{ translate('all_categories') }}">
                                            @if($categories && request('category_ids') && !empty(request('category_ids')) && !$requestAllCategoryList)
                                                @foreach($categories as $category)
                                                    @if(in_array($category->id, request('category_ids') ?? []))
                                                        {{ $category['name'] }}
                                                    @endif
                                                @endforeach
                                            @else
                                                {{ translate('all_categories') }}
                                            @endif
                                        </button>
                                        <input type="hidden" name="category_ids[]" id="search_category_value"
                                               @if($categories && request('category_ids') && !empty(request('category_ids')))
                                                   @foreach($categories as $category)
                                                       @if(in_array($category->id, request('category_ids') ?? []))
                                                           value="{{ $category->id }}"
                                                       @endif
                                                   @endforeach
                                               @else
                                                   value="{{ 'all' }}"
                                               @endif
                                        >
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="d-flex text-capitalize" data-value="all" href="javascript:">
                                                    {{ translate('all_categories') }}
                                                </a>
                                            </li>
                                            @if($categories)
                                                @foreach($categories as $category)
                                                    <li>
                                                        <a class="d-flex" data-value="{{ $category->id }}"
                                                           href="javascript:">
                                                            {{ $category['name'] }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                </div>

                                @php($searchFieldName = (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? 'search' : 'product_name')
                                <input
                                    type="search"
                                    class="form-control border-0 focus-input search-bar-input" name="{{ $searchFieldName }}"
                                    id="global-search" value="{{ request($searchFieldName) }}"
                                    placeholder="{{ (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? translate('Search_by_auction_name') : translate('search_for_items') }}"
                                />
                            </div>
                            <input name="data_from" value="search" hidden>
                            <input type="hidden" name="global_search_input" value="1">
                            <input name="page" value="1" hidden>
                            <button type="submit" class="btn btn-primary" aria-label="{{ translate('Search') }}">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                    <div
                        class="card shadow-none search-card __inline-13 position-absolute z-99 w-100 bg-white top-100 start-0 search-result-box"></div>
                </div>
                <div class="offer-btn d-flex align-items-center gap-xl-3 gap-2">
                    @if($web_config['header_banner'] && !Request::is('auction*'))
                        <a href="{{ $web_config['header_banner']['url'] }}">
                            <img width="180" loading="lazy" class="dark-support h-70 img-fit max-height-60px max-w-280px" alt="{{ translate('image') }}"
                                src="{{ getStorageImages(path: $web_config['header_banner']['photo_full_url'], type:'wide-banner') }}">
                        </a>
                    @endif

                    @if(getWebConfig(name: 'auction_feature_status') && Request::is('auction*'))
                        <div>
                            <a href="{{ route('home') }}" class="btn bg-white shadow-lg px-sm-3 px-2 py-2 back-to-ecommerce fs-14 fw-semibold text-primary d-flex align-items-center gap-2">
                                <img width="18" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/bag-forshopping.png') }}" alt="img"> {{ translate('Back to E-commerce') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="header-main love-sticky py-2 py-lg-3 py-xl-0 shadow-sm">
        <div class="container">
            <aside class="aside d-flex flex-column d-xl-none">
                <div class="aside-close p-3 pb-2 w-max-content">
                    <i class="bi bi-x-lg"></i>
                </div>
                <div>
                    <div class="aside-body" data-trigger="scrollbar">
                        <form action="{{ (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? route('auction.products') : route('products') }}" method="GET" class="mb-3">
                            <div class="search-bar">
                                @php($mobileSearchFieldName = (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? 'search' : 'name')
                                <input type="search" name="{{ $mobileSearchFieldName }}" class="form-control search-bar-input-mobile"
                                       autocomplete="off" value="{{ request($mobileSearchFieldName) }}"
                                       placeholder="{{ (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? translate('Search_by_auction_name') : translate('search_for_items') }}">
                                <input name="data_from" value="search" hidden="">
                                <input name="page" value="1" hidden="">
                                <button type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div
                                class="card search-card __inline-13 position-absolute z-99 w-100 bg-white start-0 search-result-box d--none"></div>
                        </form>
                        @if(getWebConfig(name: 'auction_feature_status') && Request::is('auction*'))
                        <div class="my-2 px-2">
                            <a href="{{ route('home') }}" class="btn bg-white border px-sm-3 px-2 py-2 back-to-ecommerce fs-14 fw-semibold text-primary d-flex align-items-center gap-2">
                                <img width="18" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/bag-forshopping.png') }}" alt="img"> {{ translate('Back to E-commerce') }}
                            </a>
                        </div>
                        @endif
                        @if(getWebConfig(name: 'auction_feature_status') && !Request::is('auction*'))
                        <div class="my-2 px-2">
                            <a href="{{ route('auction.index') }}" class="d-flex text-absolute-white btn btn-primary w-100 gap-1 align-items-center justify-content-center">
                                <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/sale-icon1.png') }}" width="18" height="18" alt="img">
                                {{ translate('Auction') }}
                            </a>
                        </div>
                        @endif
                        <ul class="main-nav nav">
                            <li>
                                <a href="{{ (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? route('auction.index') : route('home') }}">
                                    {{ translate('Home') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? route('auction.category-products') : route('categories') }}">
                                    {{ translate('categories') }}
                                </a>
                                <ul class="sub_menu">
                                    @php($categoryIndex=0)
                                    @foreach($categories as $category)
                                        @php($categoryIndex++)
                                        @if($categoryIndex < 10)
                                            <li>
                                                @if(function_exists('getCheckAddonPublishedStatus') && getCheckAddonPublishedStatus(moduleName: 'Auction') && !(getWebConfig(name: 'auction_feature_status') && Request::is('auction*')))
                                                    <a href="javascript:">
                                                    <span class="get-view-by-onclick"
                                                          data-link="{{ route('auction.category-products', ['slug' => $category['slug']]) }}">
                                                        {{ $category['name'] }}
                                                    </span>
                                                    </a>
                                                @else
                                                    <a href="javascript:">
                                                    <span class="get-view-by-onclick"
                                                          data-link="{{ route('category-products', ['slug' => $category['slug']]) }}">
                                                        {{ $category['name'] }}
                                                    </span>
                                                    </a>
                                                    @if ($category->childes->count() > 0)
                                                        <ul class="sub_menu">
                                                            @foreach($category['childes'] as $subCategory)
                                                                <li>
                                                                    <a href="javascript:">
                                                                        <span class="get-view-by-onclick" data-link="{{ route('category-products', ['slug' => $subCategory['slug']]) }}">{{$subCategory['name']}}</span>
                                                                    </a>
                                                                    @if($subCategory->childes->count()>0)
                                                                        <ul class="sub_menu">
                                                                            @foreach($subCategory['childes'] as $subSubCategory)
                                                                                <li>
                                                                                    <a href="{{ route('category-products', ['slug' => $subSubCategory['slug']]) }}">
                                                                                        {{ $subSubCategory['name'] }}
                                                                                    </a>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                @endif
                                            </li>
                                        @endif
                                    @endforeach
                                    <li>
                                        <a href="{{ (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? route('auction.products') : route('products') }}" class="btn-link text-primary">
                                            {{ translate('view_all') }}
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            @if(!(getWebConfig(name: 'auction_feature_status') && Request::is('auction*')))
                                @if(getFeaturedDealsProductList()->count() > 0 || ($web_config['flash_deals'] && count($web_config['flash_deals_products']) > 0) || $web_config['discount_product'] > 0 || $web_config['clearance_sale_product_count'] > 0)
                                    <li>
                                        <a href="javascript:">{{ translate('offers') }}</a>
                                        <ul class="sub_menu">
                                            @if(getFeaturedDealsProductList()->count() > 0)
                                                <li>
                                                    <a href="{{ route('featured-deal-products') }}">
                                                        {{ translate('featured_Deal') }}
                                                    </a>
                                                </li>
                                            @endif

                                            @if($web_config['flash_deals'] && count($web_config['flash_deals_products']) > 0)
                                                <li>
                                                    <a href="{{ route('flash-deals',['id' => $web_config['flash_deals']['id'] ?? 0]) }}">{{ translate('flash_deal') }}</a>
                                                </li>
                                            @endif
                                            @if ($web_config['discount_product'] > 0)
                                                <li>
                                                    <a class="d-flex gap-2 align-items-center"
                                                       href="{{ route('discounted-products') }}">
                                                        <span>{{ translate('discounted_products') }}</span>
                                                        <span><i class="bi bi-patch-check-fill text-warning"></i></span>
                                                    </a>
                                                </li>
                                            @endif
                                            @if($web_config['clearance_sale_product_count'] > 0)
                                                <li>
                                                    <a class="gap-2 align-items-center"
                                                       href="{{ route('clearance-sale-products') }}">
                                                        <span>{{ translate('clearance_sale') }}</span>
                                                        <span><i class="bi bi-patch-check-fill text-warning"></i></span>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                                @if($web_config['business_mode'] == 'multi')
                                    <li>
                                        <a href="javascript:">{{ translate('stores') }}</a>
                                        <ul class="sub_menu">
                                            <li>
                                                <a href="{{ route('vendor-shop', ['slug' => getInHouseShopConfig(key:'slug')]) }}">
                                                    {{ Str::limit(getInHouseShopConfig(key:'name'), 14) }}
                                                </a>
                                            </li>
                                            @foreach($web_config['shops'] as $shop)
                                                <li>
                                                    <a href="{{ route('vendor-shop',['slug' => $shop['slug']]) }}">{{Str::limit($shop->name, 14) }}</a>
                                                </li>
                                            @endforeach
                                            <li>
                                                <a href="{{ route('vendors') }}" class="btn-link text-primary">
                                                    {{ translate('view_all') }}
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                @endif
                            @endif

                            @if($web_config['brand_setting'])
                                <li>
                                    <a href="javascript:">{{ translate('brands') }}</a>
                                    <ul class="sub_menu">
                                        @php($brandIndex=0)
                                        @foreach($brands as $brand)
                                            @php($brandIndex++)
                                            @if($brandIndex < 10)
                                                <li>
                                                    <a href="{{ route('brand-products',['slug' => $brand['slug']]) }}">
                                                        {{ $brand->name }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                        <li>
                                            <a href="{{ route('brands') }}" class="btn-link text-primary">
                                                {{ translate('view_all') }}
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif

                            @if(!(getWebConfig(name: 'auction_feature_status') && Request::is('auction*')))
                                @if ($web_config['digital_product_setting'] && count($web_config['publishing_houses_header']) == 1)
                                @php($firstPublisherID = is_array($web_config['publishing_houses_header']) && isset($web_config['publishing_houses_header']['id']) ? $web_config['publishing_houses_header']['id'] : $web_config['publishing_houses_header']?->first()?->id)
                                <li>
                                    <a class="d-flex gap-2 align-items-center text-capitalize"
                                       href="{{ route('products',['data_from' => 'publishing_house', 'publishing_house_id' => $firstPublisherID, 'product_type' => 'digital', 'page'=>1]) }}">
                                        {{ translate('Publication_House') }}
                                    </a>
                                </li>
                            @elseif ($web_config['digital_product_setting'] && count($web_config['publishing_houses_header']) > 1)
                                <li>
                                    <a class="d-flex gap-2 align-items-center text-capitalize"
                                       href="{{ route('products', ['data_from' => 'publishing_house', 'product_type' => 'digital', 'page'=>1]) }}">
                                        {{ translate('Publication_House') }}
                                    </a>
                                </li>
                            @endif
                            @endif

                            @if((getWebConfig(name: 'auction_feature_status') && Request::is('auction*')))
                                <li class="d-xl-none">
                                    <a href="{{ route('auction.ending-soon-products') }}" class="d-flex gap-1 align-items-center text-capitalize">
                                        <img width="20" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/ending-soon.png') }}" alt="img" class="object-contain">
                                        <span class="fz-16 text-capitalize">{{ translate('Ending Soon') }}</span>
                                    </a>
                                </li>

                                <li class="d-xl-none">
                                    <a href="{{ route('auction.trending-products') }}" class="d-flex gap-1 align-items-center text-capitalize">
                                        <img width="20" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/trend-fire.png') }}" alt="img" class="object-contain">
                                        <span class="fz-16 text-capitalize">{{ translate('Trending') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if($web_config['business_mode'] == 'multi' &&  $web_config['seller_registration'])
                                <li class="d-xl-none">
                                    <a href="{{ route('vendor.auth.registration.index') }}" class="d-flex text-capitalize">
                                        <div class="fz-16 text-capitalize">{{ translate('become_a_vendor') }}</div>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="d-flex align-items-center gap-2 justify-content-between p-4">
                        <span class="text-dark">{{ translate('theme_mode') }}</span>
                        <div class="theme-bar p-1">
                            <button class="light_button active">
                                <img src="{{ theme_asset('assets/img/svg/light.svg') }}" alt="{{ translate('image') }}"
                                     class="svg">
                            </button>
                            <button class="dark_button">
                                <img src="{{ theme_asset('assets/img/svg/dark.svg') }}" alt="{{ translate('image') }}"
                                     class="svg">
                            </button>
                        </div>
                    </div>
                </div>

                @if(auth('customer')->check())
                    <div class="d-flex justify-content-center mb-5 pb-5 mt-auto px-4">
                        <a href="{{ route('customer.auth.logout') }}"
                           class="btn btn-primary w-100">{{ translate('logout') }}</a>
                    </div>
                @else
                    <div class="d-flex justify-content-center mb-5 pb-5 mt-auto px-4">
                        <a href="" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn btn-primary text-absolute-white w-100" aria-label="{{ translate('login').'/'.translate('register') }}">
                            {{ translate('login').'/'.translate('register') }}
                        </a>
                    </div>
                @endif
            </aside>
            <div class="aside-overlay"></div>

            <div class="d-flex justify-content-between gap-3 align-items-center position-relative">
                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown d-none d-xl-block">
                        <button
                            class="btn btn-primary rounded-0 text-uppercase fw-bold fs-14 dropdown-toggle select-category-button text-capitalize"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-list fs-4"></i>
                            {{ translate('select_category') }}
                        </button>
                        <ul class="dropdown-menu dropdown--menu">
                            @php($categoryDropdownKeyIndex=0)
                            @foreach($categories as $category)
                                @if($categoryDropdownKeyIndex < 11)
                                    @php($categoryDropdownKeyIndex++)
                                    <li class="{{ !(getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) && $category->childes->count() > 0 ? 'menu-item-has-children':'' }}">
                                        @if(getWebConfig(name: 'auction_feature_status') && Request::is('auction*'))
                                            <a href="{{ route('auction.category-products', ['slug' => $category['slug']]) }}">
                                                {{ $category['name'] }}
                                            </a>
                                        @else
                                            <a href="{{ route('category-products', ['slug' => $category['slug']]) }}">
                                                {{$category['name']}}
                                            </a>
                                            @if ($category->childes->count() > 0)
                                            <ul class="sub-menu">
                                                @foreach($category['childes'] as $subCategory)
                                                    <li class="{{ $subCategory->childes->count()>0 ? 'menu-item-has-children':'' }}">
                                                        <a href="{{ route('category-products', ['slug' => $subCategory['slug']]) }}">
                                                            {{$subCategory['name']}}
                                                        </a>
                                                        @if($subCategory->childes->count()>0)
                                                            <ul class="sub-menu">
                                                                @foreach($subCategory['childes'] as $subSubCategory)
                                                                    <li>
                                                                        <a href="{{ route('category-products', ['slug' => $subSubCategory['slug']]) }}">
                                                                            {{$subSubCategory['name']}}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                            <li>
                                <a href="{{ (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? route('auction.products') : route('products') }}" class="btn-link text-primary">
                                    {{ translate('view_all') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="nav-wrapper">
                        <div class="d-xl-none">
                            <div class="logo-area d-flex align-items-center gap-2">
                                <a class="system-logo-element flex-shrink-0" href="{{ (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? route('auction.index') : route('home') }}">
                                    <img class="dark-support" alt="{{ translate('Logo') }}"
                                        src="{{ getStorageImages(path: $web_config['mob_logo'], type:'logo') }}">
                                </a>

                                @if(getWebConfig(name: 'auction_feature_status') && Request::is('auction*'))
                                <span class="badge text-white bg-warning-dark rounded-pill fs-12 fw-semibold mx-1">{{ translate('Auction') }}</span>
                                @endif

                            </div>
                        </div>
                        <ul class="nav main-menu align-items-center d-none d-xl-flex flex-nowrap">
                            <li class="{{request()->is('/')?'active':''}}">
                                <a href="{{ (getWebConfig(name: 'auction_feature_status') && Request::is('auction*')) ? route('auction.index') : route('home') }}">
                                    {{ translate('Home') }}
                                </a>
                            </li>

                            @if(getWebConfig(name: 'auction_feature_status') && Request::is('auction*'))
                                @if($web_config['brand_setting'])
                                    <li>
                                        <span class="cursor-pointer no-follow-link" ref="nofollow">{{ translate('brands') }}</span>
                                        <div class="sub-menu megamenu p-3 bs-dropdown-min-width--max-content">
                                            <div class="d-flex gap-4">
                                                <div class="column-2">
                                                    @php($brandSecondIndex=0)
                                                    @foreach(\Modules\Auction\app\Utils\AuctionProductManager::getAuctionActiveBrandWithCounting() as $brand)
                                                        @php($brandSecondIndex++)
                                                        @if($brandSecondIndex < 10)
                                                            <a href="{{ route('auction.brands-products', ['slug' => $brand['slug']]) }}"
                                                               class="media gap-3 align-items-center border-bottom">
                                                                <div class="avatar rounded-circle size-1-25rem">
                                                                    <img class="img-fit rounded-circle dark-support"
                                                                         src="{{ getStorageImages(path: $brand->image_full_url, type: 'brand') }}"
                                                                         loading="lazy" alt="{{ $brand?->image_alt_text ?? translate('Brand') }}"/>
                                                                </div>
                                                                <div class="media-body text-truncate width--7rem">
                                                                    {{ $brand->name }}
                                                                </div>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                    <div class="d-flex">
                                                        <a href="{{ route('auction.brands-products') }}"
                                                           class="fw-bold text-primary d-flex justify-content-center">{{ translate('view_all').'...' }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                <li class="{{ request()->is('auction/ending-soon-products*') ? 'active' : '' }}">
                                    <a href="{{ route('auction.ending-soon-products') }}" class="d-flex gap-1 align-items-center">
                                        <img width="20" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/ending-soon.png') }}" alt="img" class="object-contain">
                                        <span>{{ translate('Ending Soon') }}</span>
                                    </a>
                                </li>

                                <li class="{{ request()->is('auction/trending-products*') ? 'active' : '' }}">
                                    <a href="{{ route('auction.trending-products') }}" class="d-flex gap-1 align-items-center">
                                        <img width="20" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/trend-fire.png') }}" alt="img" class="object-contain">
                                        <span>{{ translate('Trending') }}</span>
                                    </a>
                                </li>
                            @else
                                @if(getFeaturedDealsProductList()->count() > 0 || ($web_config['flash_deals'] && count($web_config['flash_deals_products']) > 0) || $web_config['discount_product'] > 0)
                                    <li>
                                        <span class="cursor-pointer no-follow-link" ref="nofollow">{{ translate('offers') }}</span>
                                        <ul class="sub-menu">
                                            @if(getFeaturedDealsProductList()->count()>0)
                                                <li>
                                                    <a class="text-capitalize"
                                                       href="{{ route('featured-deal-products') }}">
                                                        {{ translate('featured_deal') }}
                                                    </a>
                                                </li>
                                            @endif

                                            @if($web_config['flash_deals'] && count($web_config['flash_deals_products']) > 0)
                                                <li>
                                                    <a class="text-capitalize"
                                                       href="{{ route('flash-deals',['id' => $web_config['flash_deals']['id']??0]) }}">{{ translate('flash_deal') }}</a>
                                                </li>
                                            @endif
                                            @if ($web_config['discount_product'] > 0)
                                                <li>
                                                    <a class="gap-2 d-flex align-items-center text-capitalize"
                                                       href="{{ route('discounted-products') }}">
                                                        <span>{{ translate('discounted_products') }}</span>
                                                        <span><i class="bi bi-patch-check-fill text-warning"></i></span>
                                                    </a>
                                                </li>
                                            @endif

                                            @if($web_config['clearance_sale_product_count'] > 0)
                                                <li>
                                                    <a class="gap-2 d-flex align-items-center"
                                                       href="{{ route('clearance-sale-products') }}">
                                                        <span>{{ translate('clearance_sale') }}</span>
                                                        <span><i class="bi bi-patch-check-fill text-warning"></i></span>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif

                                @if($web_config['business_mode'] == 'multi')
                                    <li>
                                        <span class="cursor-pointer no-follow-link" ref="nofollow">{{ translate('stores') }}</span>
                                        <div
                                            class="sub-menu megamenu p-3 bs-dropdown-min-width--max-content">
                                            <div class="d-flex gap-1">
                                                <div>
                                                    <div class="column-2 row-gap-3">
                                                        <a href="{{ route('vendor-shop', ['slug' => getInHouseShopConfig(key:'slug')]) }}"
                                                           class="media gap-3 align-items-center border-bottom">
                                                            <div class="avatar rounded size-2-5rem">
                                                                <img loading="lazy" alt="{{ translate('image') }}"
                                                                     src="{{ getStorageImages(path: getInHouseShopConfig(key:'image_full_url'), type:'shop') }}"
                                                                     class="img-fit rounded dark-support overflow-hidden">
                                                            </div>
                                                            <div class="media-body text-truncate width--7rem"
                                                                 title="{{ getInHouseShopConfig(key:'name') }}">
                                                                {{ Str::limit(getInHouseShopConfig(key:'name'), 14) }}
                                                            </div>
                                                        </a>
                                                        @foreach($web_config['shops'] as $shop)
                                                            <a href="{{ route('vendor-shop', ['slug' => $shop['slug']]) }}"
                                                               class="media gap-3 align-items-center border-bottom">
                                                                <div class="avatar rounded size-2-5rem">
                                                                    <img loading="lazy" alt="{{ translate('image') }}"
                                                                         src="{{ getStorageImages(path: $shop->image_full_url, type: 'shop') }}"
                                                                         class="img-fit rounded dark-support overflow-hidden">
                                                                </div>
                                                                <div class="media-body text-truncate width--7rem"
                                                                     title="{{ $shop->name }}">
                                                                    {{Str::limit($shop->name, 14) }}
                                                                </div>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                    <div class="d-flex">
                                                        <a href="{{ route('vendors') }}"
                                                           class="fw-bold text-primary d-flex justify-content-center">
                                                            {{ translate('view_all').'...' }}
                                                        </a>
                                                    </div>
                                                </div>
                                                <div>
                                                    <a href="javascript:">
                                                        <img
                                                            width="277"
                                                            src="{{ theme_asset('assets/img/media/super-market.webp') }}"
                                                            class="dark-support"
                                                            alt="{{ translate('image') }}"
                                                        />
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                @if($web_config['brand_setting'])
                                    <li>
                                        <span class="cursor-pointer no-follow-link" ref="nofollow">{{ translate('brands') }}</span>
                                        <div class="sub-menu megamenu p-3 bs-dropdown-min-width--max-content">
                                            <div class="d-flex gap-4">
                                                <div class="column-2">
                                                    @php($brandSecondIndex=0)
                                                    @foreach($brands as $brand)
                                                        @php($brandSecondIndex++)
                                                        @if($brandSecondIndex < 10)
                                                            <a href="{{ route('brand-products', ['slug' => $brand['slug']]) }}"
                                                               class="media gap-3 align-items-center border-bottom">
                                                                <div class="avatar rounded-circle size-1-25rem">
                                                                    <img class="img-fit rounded-circle dark-support"
                                                                         src="{{ getStorageImages(path: $brand->image_full_url, type: 'brand') }}"
                                                                         loading="lazy" alt="{{ $brand?->image_alt_text ?? translate('Brand') }}"/>
                                                                </div>
                                                                <div class="media-body text-truncate width--7rem">
                                                                    {{ $brand->name }}
                                                                </div>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                    <div class="d-flex">
                                                        <a href="{{ route('brands') }}"
                                                           class="fw-bold text-primary d-flex justify-content-center">{{ translate('view_all').'...' }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                @if ($web_config['digital_product_setting'] && count($web_config['publishing_houses_header']) == 1)
                                    <li>
                                        <a href="{{ route('products',['data_from' => 'publishing_house', 'publishing_house_id' => 0, 'product_type' => 'digital', 'page'=>1]) }}">
                                            {{ translate('Publication_House') }}
                                        </a>
                                    </li>
                                @elseif ($web_config['digital_product_setting'] && count($web_config['publishing_houses_header']) > 1)
                                    <li>
                                        <a class="cursor-pointer" href="{{ route('products', ['product_type' => 'digital', 'page'=>1]) }}">
                                            {{ translate('Publication_House') }}
                                        </a>
                                        <div class="sub-menu megamenu p-3 bs-dropdown-min-width--max-content">
                                            <div class="d-flex gap-4">
                                                <div class="column-2">
                                                    @php($publishingHousesIndex=0)
                                                    @foreach($web_config['publishing_houses_header'] as $publishingHouseItem)
                                                        @if($publishingHousesIndex < 10 && $publishingHouseItem['name'] != 'Unknown')
                                                            @php($publishingHousesIndex++)
                                                            <a href="{{ route('products',['data_from' => 'publishing_house', 'publishing_house_id'=> $publishingHouseItem['id'], 'product_type' => 'digital', 'page'=>1]) }}"
                                                               class="media gap-3 align-items-center border-bottom">
                                                                <div class="media-body text-truncate width--7rem">
                                                                    {{ $publishingHouseItem['name'] }}
                                                                </div>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                    <div class="d-flex">
                                                        <a href="{{ route('products', ['data_from' => 'publishing_house', 'product_type' => 'digital', 'page' => 1]) }}"
                                                           class="fw-bold text-primary d-flex justify-content-center">
                                                            {{ translate('view_all').'...' }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            @endif
                            @if((getWebConfig(name: 'auction_feature_status') && !(Request::is('auction*'))))
                            <li>
                                <a href="{{ route('auction.index') }}" class="d-flex text-absolute-white bg-primary py-1 px-2 rounded text-uppercase fw-normal gap-1 align-items-center justify-content-center">
                                    <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/sale-icon1.png') }}" width="18" height="18" alt="img">
                                    {{ translate('Auction') }}
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
                <ul class="list-unstyled list-separator mb-0 pe-2">
                    @if(auth('customer')->check())
                        <li class="login-register d-flex align-items-center gap-4">
                            @if(function_exists('getCheckAddonPublishedStatus') && getCheckAddonPublishedStatus(moduleName: 'Auction'))
                            <div class="d-md-none d-flex align-items-center gap-2">
                                <a class="btn p-0 position-relative outline-0 border-0" href="{{ route('auction.bids.list') }}">
                                    <i class="fi fi-sr-auction fs-18 text-dark"></i>
                                    <span class="status position-absolute"></span>
                                </a>
                            </div>
                            @endif
                            <div class="profile-dropdown">
                                <button
                                    type="button"
                                    class="border-0 bg-transparent d-flex gap-2 align-items-center text-dark p-0 user"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    <span class="avatar overflow-hidden header-avatar rounded-circle size-1-5rem border border-primary d-flex">
                                        @php($profileImg = getCustomerFromQuery() ? getCustomerFromQuery()->image_full_url : '')
                                        <img loading="lazy" class="img-fit" alt="{{ translate('image') }}"
                                          src="{{ getStorageImages(path: $profileImg, type:'avatar') }}">
                                    </span>
                                </button>
                                <ul class="dropdown-menu bs-dropdown-min-width--10rem header-dropdown">
                                    @if(getWebConfig(name: 'auction_feature_status') && Request::is('auction*'))
                                        <li class="fs-14 fw-medium">
                                            <a href="{{ route('auction.bids.list') }}">
                                                {{ translate('My_Bids') }}
                                            </a>
                                        </li>
                                    @else
                                        <li><a href="{{ route('account-oder') }}">{{ translate('My_Order') }}</a></li>
                                    @endif
                                    <li class="fs-14 fw-medium"><a href="{{ route('user-profile') }}">{{ translate('My_Profile') }}</a></li>
                                    <li class="fs-14 fw-medium"><a href="{{ route('customer.auth.logout') }}">{{ translate('Logout') }}</a></li>
                                </ul>
                            </div>
                            <div class="menu-btn d-xl-none">
                                <i class="bi bi-list fs-30 text-dark"></i>
                            </div>
                        </li>
                    @else
                        <li class="login-register d-flex align-items-center gap-4">
                            <button
                                class="media gap-2 align-items-center text-uppercase fs-12 bg-transparent border-0 p-0"
                                data-bs-toggle="modal"
                                data-bs-target="#loginModal"
                            >
                                <span class="avatar header-avatar rounded-circle d-xl-none size-1-5rem">
                                    <img
                                        loading="lazy"
                                        src="{{ theme_asset('assets/img/user.png') }}"
                                        class="img-fit rounded-circle"
                                        alt="{{ translate('image') }}"
                                    />
                              </span>
                                <span
                                    class="media-body d-none d-xl-block hover-primary">{{ translate('login').'/'. translate('register') }}</span>
                            </button>
                            <div class="menu-btn d-xl-none">
                                <i class="bi bi-list fs-30 text-dark"></i>
                            </div>
                        </li>
                    @endif
                    <li class="d-none d-xl-block">
                        @if(getWebConfig(name: 'auction_feature_status') && Request::is('auction*'))
                            @if(auth('customer')->check())
                                <a href="{{ route('auction.saved-products-list') }}" class="position-relative">
                                    <i class="bi bi-bookmark text-primary fs-16"></i>
                                    <span class="count auction-saved-update-count">
                                        {{ \Modules\Auction\app\Models\AuctionSavedProduct::where('user_id', auth('customer')->id())->whereHas('auctionProduct', fn($q) => $q->active())->count() }}
                                    </span>
                                </a>
                            @else
                                <a href="javascript:" class="position-relative" data-bs-toggle="modal"
                                   data-bs-target="#loginModal">
                                    <i class="bi bi-bookmark text-primary fs-16"></i>
                                </a>
                            @endif
                        @else
                            @if(auth('customer')->check())
                                <a href="{{ route('product-compare.index') }}" class="position-relative">
                                    <i class="bi bi-repeat fs-18"></i>
                                    <span class="count compare_list_count_status">
                                        {{ session()->has('compare_list')?count(session('compare_list')) : 0 }}
                                    </span>
                                </a>
                            @else
                                <a href="javascript:" class="position-relative" data-bs-toggle="modal"
                                   data-bs-target="#loginModal">
                                    <i class="bi bi-repeat fs-18"></i>
                                </a>
                            @endif
                        @endif
                    </li>

                    @if(!getWebConfig(name: 'auction_feature_status') || !Request::is('auction*'))
                        <li class="d-none d-xl-block">
                            @if(auth('customer')->check())
                                <a href="{{ route('wishlists') }}" class="position-relative">
                                    <i class="bi bi-heart fs-18"></i>
                                    <span class="count wishlist_count_status">
                                        {{ session()->has('wish_list') ? count(session('wish_list')) : 0 }}
                                    </span>
                                </a>
                            @else
                                <a href="javascript:" class="position-relative" data-bs-toggle="modal"
                                   data-bs-target="#loginModal">
                                    <i class="bi bi-heart fs-18"></i>
                                </a>
                            @endif
                        </li>
                        <li class="d-none d-xl-block" id="cart_items">
                            @include('theme-views.layouts.partials._cart')
                        </li>
                    @endif

                    @if(getWebConfig(name: 'auction_feature_status') && Request::is('auction*'))
                        <li class="d-md-block d-none">
                            @if(auth('customer')->check())
                                <button class="btn p-0 position-relative outline-0 border-0" type="button"
                                        data-bs-toggle="offcanvas" data-bs-target="#auction-notification-offcanvas">
                                    <i class="fi fi-rr-bell fs-18 text-primary"></i>
                                    @if(($unreadNotificationCount ?? 0) > 0)
                                        <span class="badge bg-danger position-absolute rounded-pill"
                                              style="top:-4px;right:-4px;font-size:9px;padding:2px 4px;min-width:16px;line-height:1.2;">
                                            {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                        </span>
                                    @endif
                                </button>
                            @else
                                <a href="javascript:" data-bs-toggle="modal" data-bs-target="#loginModal"
                                   class="btn p-0 position-relative outline-0 border-0">
                                    <i class="fi fi-rr-bell fs-18 text-primary"></i>
                                </a>
                            @endif
                        </li>
                        <li class="d-md-flex d-none align-items-center gap-2">
                            @if(auth('customer')->check())
                                <a class="btn p-0 position-relative outline-0 border-0"
                                   href="{{ route('auction.bids.list') }}">
                                    <i class="fi fi-sr-auction fs-18 text-dark"></i>
                                    <span class="status position-absolute"></span>
                                </a>
                            @else
                                <a href="javascript:" data-bs-toggle="modal" data-bs-target="#loginModal"
                                   class="btn p-0 position-relative outline-0 border-0">
                                    <i class="fi fi-sr-auction fs-18 text-dark"></i>
                                </a>
                            @endif
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</header>

@if(getWebConfig(name: 'auction_feature_status') && Request::is('auction*'))


{{-- Notification Offcanvas --}}
<div class="offcanvas dark-support_offcanvas offcanvas {{ Session::get('direction') === 'rtl' ? 'offcanvas-start' : 'offcanvas-end' }}" tabindex="-1" id="auction-notification-offcanvas"
     aria-labelledby="auction-notification-offcanvasLabel" style="max-width:420px;width:100%;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fs-16 fw-semibold mb-0" id="auction-notification-offcanvasLabel">
            {{ translate('Notifications') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                aria-label="{{ translate('Close') }}"></button>
    </div>
    <div class="offcanvas-body p-3" id="auction-notification-body">
        @auth('customer')
            @if(($groupedAuctionNotifications ?? collect())->isNotEmpty())
                <div class="d-flex flex-column gap-3" id="auction-notifications-list">
                    @foreach($groupedAuctionNotifications as $groupLabel => $notifications)
                        <div>
                            <span class="text-muted fs-12 fw-semibold text-uppercase d-block mb-2">
                                {{ $groupLabel }}
                            </span>
                            <div class="d-flex flex-column gap-2">
                                @foreach($notifications as $notification)
                                    <div class="auction-notification-item rounded-2 p-3 d-flex align-items-start gap-3
                                                {{ $notification->is_read
                                                    ? 'border'
                                                    : 'notification-active border border-primary bg-primary bg-opacity-10' }}"
                                         style="cursor:pointer;"
                                         data-notification="{{ json_encode($notification->notif_data) }}">
                                        <div class="flex-shrink-0 rounded-circle overflow-hidden border"
                                             style="width:40px;height:40px;min-width:40px;">
                                            <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/congrt-bid.png') }}"
                                                 alt="" class="w-100 h-100 object-fit-cover">
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                                <h6 class="mb-0 fs-13 fw-semibold text-truncate
                                                           {{ $notification->is_read ? 'text-muted' : '' }}">
                                                    {{ $notification->notif_label }}
                                                </h6>
                                                <span class="fs-11 flex-shrink-0 auction-notif-time
                                                             {{ $notification->is_read ? 'text-muted' : 'text-primary' }}">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                            <p class="mb-0 fs-12 text-muted text-truncate">
                                                {{ $notification->message }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5" id="auction-notifications-empty">
                    <i class="fi fi-sr-bell fs-36 text-muted d-block mb-2"></i>
                    <p class="text-muted fs-14 mb-0">{{ translate('No notifications yet') }}</p>
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <p class="text-muted fs-14 mb-0">{{ translate('Please login to see notifications') }}</p>
            </div>
        @endauth
    </div>
</div>

{{-- Notification Detail Modal --}}
<div class="modal fade" id="auction-notification-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ translate('Close') }}"></button>
            </div>
            <div class="modal-body text-center px-4 pb-3 pt-1">
                <img id="aster-notif-icon"
                     src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/congrt-bid.png') }}"
                     alt="" class="rounded-circle border object-fit-cover mb-3 mx-auto d-block"
                     style="width:70px;height:70px;">
                <h5 id="aster-notif-title" class="fw-semibold fs-16 mb-2"></h5>
                <p id="aster-notif-body" class="text-muted fs-14 mb-0"></p>
            </div>
            <div id="aster-notif-product-box"
                 class="mx-4 mb-3 border rounded-2 p-3 d-flex align-items-center gap-3 d-none">
                <div class="flex-shrink-0 rounded border overflow-hidden" style="width:48px;height:48px;">
                    <img id="aster-notif-product-img"
                         src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/congrt-bid.png') }}"
                         alt="" class="w-100 h-100 object-fit-cover">
                </div>
                <div class="overflow-hidden flex-grow-1" style="min-width:0">
                    <h6 id="aster-notif-product-name" class="mb-0 fs-13 fw-semibold text-truncate"></h6>
                </div>
            </div>
            <div id="aster-notif-footer" class="modal-footer justify-content-center border-0 pt-0 d-none">
                <a id="aster-notif-cta" href="#" class="btn btn-primary px-4">
                    {{ translate('View Details') }}
                </a>
            </div>
        </div>
    </div>
</div>

@include("auction.web-views.partials._notification-item-template")

<span id="auction-notif-meta"
      data-product-url="{{ route('auction.profile-view.product-details', ['slug' => 'ROUTE_PLACEHOLDER']) }}"
      data-user-id="{{ auth('customer')->id() ?? '' }}"
      data-today-label="{{ translate('Today') }}"
      data-just-now-label="{{ translate('Just now') }}"
></span>

<script>
window.auctionNotifTitles = {!! json_encode($notifTypeLabels ?? []) !!};

(function () {
    'use strict';

    // ── Modal ─────────────────────────────────────────────────────────────────

    function openModal(notif) {
        document.getElementById('aster-notif-title').textContent = notif.title   || '';
        document.getElementById('aster-notif-body').textContent  = notif.message || '';

        var productBox = document.getElementById('aster-notif-product-box');
        if (notif.product_name) {
            document.getElementById('aster-notif-product-name').textContent = notif.product_name;
            if (notif.product_image) {
                document.getElementById('aster-notif-product-img').src = notif.product_image;
            }
            productBox.classList.remove('d-none');
        } else {
            productBox.classList.add('d-none');
        }

        var footer = document.getElementById('aster-notif-footer');
        var ctaBtn = document.getElementById('aster-notif-cta');

        if (notif.product_url) {
            ctaBtn.href = notif.product_url;
            footer.classList.remove('d-none');
        } else {
            footer.classList.add('d-none');
        }

        var modalEl = document.getElementById('auction-notification-modal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // ── Sidebar push ──────────────────────────────────────────────────────────

    function buildSidebarItem(notif) {
        var tpl = document.getElementById('auction-notif-item-template');
        if (!tpl) return null;
        var clone = tpl.content.cloneNode(true).firstElementChild;
        clone.setAttribute('data-notification', JSON.stringify(notif));
        clone.querySelector('.js-notif-title').textContent   = notif.title   || '';
        clone.querySelector('.js-notif-time').textContent    = notif.time    || '';
        clone.querySelector('.js-notif-message').textContent = notif.message || '';
        return clone;
    }

    function pushToSidebar(notif) {
        var meta       = document.getElementById('auction-notif-meta');
        var todayLabel = meta ? (meta.dataset.todayLabel || 'Today') : 'Today';

        var emptyState = document.getElementById('auction-notifications-empty');
        if (emptyState) emptyState.remove();

        var list = document.getElementById('auction-notifications-list');
        if (!list) {
            list = document.createElement('div');
            list.id        = 'auction-notifications-list';
            list.className = 'd-flex flex-column gap-3';
            var body = document.getElementById('auction-notification-body');
            if (body) body.prepend(list);
        }

        var todayItems = null;
        list.querySelectorAll(':scope > div').forEach(function (group) {
            var label = group.querySelector(':scope > span');
            if (label && label.textContent.trim() === todayLabel) {
                todayItems = group.querySelector('.d-flex.flex-column.gap-2');
            }
        });

        if (!todayItems) {
            var labelSpan = document.createElement('span');
            labelSpan.className   = 'text-muted fs-12 fw-semibold text-uppercase d-block mb-2';
            labelSpan.textContent = todayLabel;

            todayItems = document.createElement('div');
            todayItems.className = 'd-flex flex-column gap-2';

            var groupDiv = document.createElement('div');
            groupDiv.appendChild(labelSpan);
            groupDiv.appendChild(todayItems);
            list.prepend(groupDiv);
        }

        var item = buildSidebarItem(notif);
        if (item) todayItems.prepend(item);

        var bellBtn = document.querySelector('button[data-bs-target="#auction-notification-offcanvas"]');
        if (bellBtn) {
            var badge = bellBtn.querySelector('.badge.bg-danger');
            if (badge) {
                var current = parseInt(badge.textContent, 10) || 0;
                badge.textContent = current >= 99 ? '99+' : String(current + 1);
            } else {
                var newBadge = document.createElement('span');
                newBadge.className     = 'badge bg-danger position-absolute rounded-pill';
                newBadge.style.cssText = 'top:-4px;right:-4px;font-size:9px;padding:2px 4px;min-width:16px;line-height:1.2;';
                newBadge.textContent   = '1';
                bellBtn.appendChild(newBadge);
            }
        }
    }

    // ── Click: mark read + open modal ─────────────────────────────────────────

    document.addEventListener('click', function (e) {
        var item = e.target.closest('.auction-notification-item');
        if (!item) return;

        var rawData = item.getAttribute('data-notification');
        if (!rawData) return;

        var notif;
        try { notif = JSON.parse(rawData); } catch (err) { return; }

        if (item.classList.contains('notification-active')) {
            item.classList.remove('notification-active', 'border-primary', 'bg-primary', 'bg-opacity-10');
            item.classList.add('border');

            var timeSpan = item.querySelector('.auction-notif-time');
            if (timeSpan) {
                timeSpan.classList.remove('text-primary');
                timeSpan.classList.add('text-muted');
            }

            var bellBtn = document.querySelector('button[data-bs-target="#auction-notification-offcanvas"]');
            if (bellBtn) {
                var badge = bellBtn.querySelector('.badge.bg-danger');
                if (badge) {
                    var count = parseInt(badge.textContent, 10) || 1;
                    if (count <= 1) { badge.remove(); } else { badge.textContent = String(count - 1); }
                }
            }

            if (notif.id) {
                var csrf = document.querySelector('meta[name="_token"]')?.content || '';
                fetch('{{ route('auction.notifications.read', ['id' => '__ID__']) }}'.replace('__ID__', notif.id), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                }).catch(function () {});
            }
        }

        openModal(notif);
    });

    // ── Public API ────────────────────────────────────────────────────────────

    window.auctionNotificationUI = {
        openModal: openModal,
        push:      pushToSidebar,
    };
})();
</script>

<script>
// Native "x" clear on type=search inputs: when the input is emptied while the
// current URL carries a ?search filter, submit the (now empty) form to reload
// the listing without the filter.
(function () {
    'use strict';
    var inputs = document.querySelectorAll('#global-search, .search-bar-input-mobile');
    inputs.forEach(function (input) {
        input.addEventListener('search', function () {
            if (input.value === '' && new URLSearchParams(window.location.search).has('search')) {
                var form = input.closest('form');
                if (form) form.submit();
            }
        });
    });
})();
</script>

@endif
