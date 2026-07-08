<div class="p-10px pt-0">
    <div class="d-flex flex-column gap-20px">

        <div class="price-range-wrapper">
            <div class="d-flex gap-2 justify-content-between align-items-center fllex-wrap mb-3">
                <h6 class="fs-13 fw-semibold mb-0">{{ translate('Auction Ending Time') }}</h6>
                <div>
                    <select name="duration_time_type" class="form-select min-w-70px h-30px fs-11 duration_time_type" data-default-value="min">
                        <option value="min" {{ request('duration_time_type', 'min') === 'min' ? 'selected' : '' }}>{{ translate('Minutes') }}</option>
                        <option value="hour" {{ request('duration_time_type') === 'hour' ? 'selected' : '' }}>{{ translate('Hours') }}</option>
                        <option value="day" {{ request('duration_time_type') === 'day' ? 'selected' : '' }}>{{ translate('Day') }}</option>
                    </select>
                </div>
            </div>
            <div class="d-flex align-items-end flex-wrap flex-sm-nowrap gap-2">
                <div class="d-flex align-items-end gap-2">
                    <div class="form-group mb-0">
                        <label for="duration_min" class="mb-1 fs-10">{{ translate('Min') }}</label>
                        <input type="text" id="duration_min" class="form-control border py-1 fs-12 title-clr min-h-30px duration_min auction-decimal-input"
                               name="ending_time_min"
                               placeholder="{{ translate('ex: 0') }}"
                               value="{{ request('ending_time_min', 0) }}"
                               inputmode="decimal" autocomplete="off"
                               data-default-value="0">
                    </div>
                    <div class="mb-2">-</div>
                    <div class="form-group mb-0">
                        <label for="duration_max" class="mb-1 fs-10">{{ translate('Max') }}</label>
                        <input type="text" id="duration_max" class="form-control border py-1 fs-12 title-clr min-h-30px duration_max auction-decimal-input"
                               name="ending_time_max"
                               value="{{ request('ending_time_max', $endingTimeRange['max_min'] ?? 60) }}"
                               placeholder="{{ translate('ex: 60') }}"
                               inputmode="decimal" autocomplete="off"
                               data-default-value="{{ $endingTimeRange['max_min'] ?? 60 }}">
                    </div>
                    <button type="button" class="btn btn-primary p-1 border-0 w-30px h-30px min-w-30px fs-12 action-search-products-by-price">
                        <i class="fi fi-rr-angle-right"></i>
                    </button>
                </div>
            </div>

            <div class="duration_range_slider range_slider_div mt-3 mb-1 rounded-10"
                 data-max-min="{{ $endingTimeRange['max_min'] ?? 60 }}"
                 data-max-hour="{{ $endingTimeRange['max_hour'] ?? 24 }}"
                 data-max-day="{{ $endingTimeRange['max_day'] ?? 7 }}"
                 data-min-value="0"
                 data-max-value="{{ $endingTimeRange['max_min'] ?? 60 }}">
                <div class="slider-range duration_slider_range"></div>
                <div class="slider-thumb duration_thumb_min" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('Min') }}"></div>
                <div class="slider-thumb duration_thumb_max" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('Max') }}"></div>
            </div>
        </div>

        @if(getWebConfig(name: 'auction_entry_fee_amount_status') && getWebConfig(name: 'auction_entry_fee_amount_value') > 0 && $entryFeeMaxRange > 0)
        <div>
            <div class="price-range-wrapper">
                <h6 class="fs-13 fw-semibold mb-15">{{ translate('Auction Entry Fee') }}</h6>
                <div class="">
                    <div class="d-flex align-items-end gap-2 mt-1">
                        <div class="form-group mb-0">
                            <label class="fs-10">{{ translate('Min') }}</label>
                            <input type="text" name="min_entry_fee" value="{{ request('min_entry_fee') }}" class="min_price auction-decimal-input form-control border py-1 fs-12 title-clr min-h-30px" placeholder="" inputmode="decimal" autocomplete="off" data-default-value="{{ $entryFeeMinRange ?? 0 }}">
                        </div>
                        <div class="mb-2">-</div>
                        <div class="form-group mb-0">
                            <label class="fs-10">{{ translate('Max') }}</label>
                            <input type="text" name="max_entry_fee" value="{{ request('max_entry_fee') }}" class="max_price auction-decimal-input form-control border py-1 fs-12 title-clr min-h-30px" placeholder="" inputmode="decimal" autocomplete="off" data-default-value="{{ $entryFeeMaxRange ?? 1000 }}">
                        </div>
                        <button type="button" class="btn btn-primary p-1 border-0 w-30px h-30px min-w-30px fs-12 action-search-products-by-price">
                            <i class="fi fi-rr-angle-right"></i>
                        </button>
                    </div>
                    <div class="price_range_slider mt-3 mb-1 rounded-10"
                         data-min-value="{{ $entryFeeMinRange ?? 0 }}"
                         data-max-value="{{ $entryFeeMaxRange ?? 1000 }}">
                        <div class="slider-range"></div>
                        <div class="slider-thumb thumb_min" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('Min') }}"></div>
                        <div class="slider-thumb thumb_max" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('Max') }}"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div>
            <div class="price-range-wrapper">
                <h6 class="fs-13 fw-semibold mb-15">{{ translate('Price Range') }}</h6>
                <div class="">
                    <div class="d-flex align-items-end gap-2 mt-1">
                        <div class="form-group mb-0">
                            <label class="fs-10">{{ translate('Min') }}</label>
                            <input type="text" name="min_price" value="{{ request('min_price') }}" class="min_price auction-decimal-input form-control border py-1 fs-12 title-clr min-h-30px" placeholder="" inputmode="decimal" autocomplete="off" data-default-value="{{ $priceRange['min'] ?? 0 }}">
                        </div>
                        <div class="mb-2">-</div>
                        <div class="form-group mb-0">
                            <label class="fs-10">{{ translate('Max') }}</label>
                            <input type="text" name="max_price" value="{{ request('max_price') }}" class="max_price auction-decimal-input form-control border py-1 fs-12 title-clr min-h-30px" placeholder="" inputmode="decimal" autocomplete="off" data-default-value="{{ $priceRange['max'] ?? 1000 }}">
                        </div>
                        <button type="button" class="btn btn-primary p-1 border-0 w-30px h-30px min-w-30px fs-12 action-search-products-by-price">
                            <i class="fi fi-rr-angle-right"></i>
                        </button>
                    </div>
                    <div class="price_range_slider mt-3 mb-1 rounded-10"
                         data-max-value="{{ $priceRange['max'] ?? 1000 }}"
                         data-min-value="{{ $priceRange['min'] ?? 0 }}">
                        <div class="slider-range"></div>
                        <div class="slider-thumb thumb_min" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('Min') }}"></div>
                        <div class="slider-thumb thumb_max" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('Max') }}"></div>
                    </div>
                </div>
            </div>
        </div>

        @if(!request()->routeIs('auction.ending-soon-products', 'auction.trending-products', 'auction.upcoming-products'))
        @php($selectedListingTypes = (array) request('listing_types', []))
        @if(empty($selectedListingTypes) && request('listing_type'))
            @php($selectedListingTypes = [request('listing_type')])
        @endif
        @if(empty($selectedListingTypes) && request('list_type'))
            @php($selectedListingTypes = [request('list_type')])
        @endif
        <div class="">
            <h6 class="fs-13 fw-semibold mb-15">{{ translate('Auction Listings') }}</h6>
            <div class="for-auction-listing webkit-scrolling-custom d-flex flex-column gap-20px p-10px">
                <div class="form-check">
                    <input class="form-check-input form-check-input_theme auction-listing-filter" type="checkbox"
                           name="listing_types[]" value="ending_soon" id="auction_listing_ending_soon"
                           {{ in_array('ending_soon', $selectedListingTypes, true) ? 'checked' : '' }}>
                    <label class="form-check-label mb-0 lh-24px fs-13 title-clr" for="auction_listing_ending_soon">
                        {{ translate('Ending Soon') }}
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input form-check-input_theme auction-listing-filter" type="checkbox"
                           name="listing_types[]" value="trending" id="auction_listing_trending"
                           {{ in_array('trending', $selectedListingTypes, true) || in_array('trending_products', $selectedListingTypes, true) ? 'checked' : '' }}>
                    <label class="form-check-label mb-0 lh-24px fs-13 title-clr" for="auction_listing_trending">
                        {{ translate('Trending') }}
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input form-check-input_theme auction-listing-filter" type="checkbox"
                           name="listing_types[]" value="live" id="auction_listing_live"
                           {{ in_array('live', $selectedListingTypes, true) ? 'checked' : '' }}>
                    <label class="form-check-label mb-0 lh-24px fs-13 title-clr" for="auction_listing_live">
                        {{ translate('Live') }}
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input form-check-input_theme auction-listing-filter" type="checkbox"
                           name="listing_types[]" value="upcoming" id="auction_listing_upcoming"
                           {{ in_array('upcoming', $selectedListingTypes, true) || in_array('upcoming_products', $selectedListingTypes, true) ? 'checked' : '' }}>
                    <label class="form-check-label mb-0 lh-24px fs-13 title-clr" for="auction_listing_upcoming">
                        {{ translate('Upcoming') }}
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input form-check-input_theme auction-listing-filter" type="checkbox"
                           name="listing_types[]" value="no_bids_yet" id="auction_listing_no_bids_yet"
                           {{ in_array('no_bids_yet', $selectedListingTypes, true) ? 'checked' : '' }}>
                    <label class="form-check-label mb-0 lh-24px fs-13 title-clr" for="auction_listing_no_bids_yet">
                        {{ translate('No Bids Yet') }}
                    </label>
                </div>
            </div>
        </div>
        @endif

        <div class="">
            <h6 class="fs-13 fw-semibold mb-15">{{ translate('Categories') }}</h6>
            <div class="product-categories-list webkit-scrolling-custom ps-2">
                @forelse($categories ?? [] as $category)
                    @if(($category->auction_product_count ?? 0) > 0)
                        <div class="menu--caret-accordion ps-xl-1 pe-xl-1">
                            <div class="card-header menu--caret-box ps-2 py-10 border-bottom d-flex align-items-center gap-1 justify-content-between sub-header">
                                <a href="#0"
                                   data-category-id="{{ $category->id }}"
                                   onclick="auctionSetCategoryFilter({{ $category->id }}); return false;"
                                   class="cursor--pointer d-flex gap-10px align-items-center title-clr">
                                    <span class="line--limit-1 fs-12 {{ request('category_id') == $category->id ? 'auction-filter-active-text' : 'fw-normal' }}">{{ $category->name }}</span>
                                </a>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="fs-12 title-clr text-center py-10">{{ translate('No_categories_found') }}</p>
                @endforelse
            </div>
        </div>

        <div class="auction-brand-filter-wrapper">
            <h6 class="fs-13 fw-semibold mb-15">{{ translate('Brands') }}</h6>
            <div class="w-100 form-auction overflow-hidden rounded border d-flex align-items-center position-relative mb-15">
                <input class="form-control min-h-40 rounded-0 border-0 auction-brand-search-input" type="text"
                       placeholder="{{ translate('Search_by_name') }}" autocomplete="off">
                <button class="btn btn-primary bg-white text-primary border-0 min-h-40 w-50px rounded-0 d-center" type="button">
                    <i class="fi fi-rr-search"></i>
                </button>
            </div>
            <ul class="for-brand webkit-scrolling-custom d-flex flex-column gap-10px list-group p-0 auction-brand-filter-list">
                @forelse($activeBrands ?? [] as $brand)
                    <li class="d-flex align-items-center gap-1 justify-content-between cursor--pointer px-2 auction-brand-item {{ request('brand_id') == $brand->id ? 'auction-filter-active-item' : '' }}"
                        data-brand-id="{{ $brand->id }}">
                        <div class="text-start fs-13 line--limit-1 auction-brand-item-name {{ request('brand_id') == $brand->id ? 'auction-filter-active-text' : 'title-clr textclr' }}">
                            {{ $brand->name }}
                        </div>
                        <div class="badge bg-light fs-12 fw-normal rounded-pill title-clr py-2px px-2 flex-shrink-0">
                            <span>{{ $brand->auction_product_count }}</span>
                        </div>
                    </li>
                @empty
                    <li class="fs-12 title-clr text-center py-10 list-unstyled">{{ translate('No_brands_found') }}</li>
                @endforelse
            </ul>
            <div class="auction-brand-no-result fs-12 title-clr text-center py-10" style="display:none;">
                {{ translate('No_brands_found') }}
            </div>
        </div>

    </div>
</div>

<script>
    (function () {
        document.querySelectorAll('.auction-brand-filter-wrapper').forEach(function (wrapper) {
            if (wrapper.dataset.jsBound === '1') return;
            wrapper.dataset.jsBound = '1';

            var searchInput = wrapper.querySelector('.auction-brand-search-input');
            var brandItems  = wrapper.querySelectorAll('.auction-brand-item');

            if (searchInput) {
                var filterBrands = function () {
                    var search = (searchInput.value || '').toLowerCase().trim();
                    var found = false;
                    brandItems.forEach(function (li) {
                        var nameEl = li.querySelector('.auction-brand-item-name');
                        var text = (nameEl ? nameEl.textContent : li.textContent).toLowerCase();
                        var visible = text.indexOf(search) !== -1;
                        li.classList.toggle('d-none', !visible);
                        if (visible) found = true;
                    });
                    var emptyState = wrapper.querySelector('.auction-brand-no-result');
                    if (emptyState) emptyState.style.display = (search === '' || found) ? 'none' : '';
                };
                searchInput.addEventListener('input', filterBrands);
                searchInput.addEventListener('keyup', filterBrands);
            }

            brandItems.forEach(function (item) {
                item.addEventListener('click', function () {
                    auctionSetBrandFilter(this.getAttribute('data-brand-id'));
                });
            });
        });
    })();

    function auctionSetCategoryFilter(categoryId) {
        var form = document.querySelector('.product-list-filter');
        if (!form) return;

        var input = form.querySelector('input[name="category_id"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'category_id';
            form.appendChild(input);
        }

        input.value = (input.value == categoryId) ? '' : categoryId;

        var brandInput = form.querySelector('input[name="brand_id"]');
        if (brandInput) brandInput.value = '';
        document.querySelectorAll('.auction-brand-item').forEach(function (li) {
            li.classList.remove('auction-filter-active-item');
            var nameEl = li.querySelector('.auction-brand-item-name');
            if (nameEl) {
                nameEl.classList.remove('auction-filter-active-text');
                nameEl.classList.add('title-clr', 'textclr');
            }
        });

        var dataFromInput = form.querySelector('input[name="data_from"]');
        if (dataFromInput) {
            dataFromInput.value = input.value ? 'category' : '';
        }

        var pageInput = form.querySelector('input[name="page"]');
        if (pageInput) pageInput.value = 1;

        var activeCategoryId = String(input.value);
        document.querySelectorAll('.product-categories-list [data-category-id]').forEach(function (a) {
            var span = a.querySelector('span');
            if (!span) return;
            var isActive = activeCategoryId !== '' && String(a.dataset.categoryId) === activeCategoryId;
            if (isActive) {
                span.classList.add('auction-filter-active-text');
                span.classList.remove('fw-normal');
            } else {
                span.classList.remove('auction-filter-active-text');
                span.classList.add('fw-normal');
            }
        });

        if (typeof window.auctionRenderProductsList === 'function') {
            window.auctionRenderProductsList();
            return;
        }
        form.submit();
    }

    function auctionSetBrandFilter(brandId) {
        var form = document.querySelector('.product-list-filter');
        if (!form) return;

        var brandInput = form.querySelector('input[name="brand_id"]');
        var dataFrom   = form.querySelector('input[name="data_from"]');

        if (brandInput) brandInput.value = (brandInput.value == brandId) ? '' : brandId;
        if (dataFrom)   dataFrom.value   = (brandInput && brandInput.value) ? 'brand' : '';

        var catInput = form.querySelector('input[name="category_id"]');
        if (catInput) catInput.value = '';
        document.querySelectorAll('.product-categories-list [data-category-id]').forEach(function (a) {
            var span = a.querySelector('span');
            if (span) {
                span.classList.remove('auction-filter-active-text');
                span.classList.add('fw-normal');
            }
        });

        var pageInput = form.querySelector('input[name="page"]');
        if (pageInput) pageInput.value = 1;

        var activeBrandId = brandInput ? String(brandInput.value) : '';
        document.querySelectorAll('.auction-brand-item').forEach(function (li) {
            var nameEl = li.querySelector('.auction-brand-item-name');
            if (!nameEl) return;
            var isActive = activeBrandId !== '' && String(li.dataset.brandId) === activeBrandId;
            if (isActive) {
                nameEl.classList.add('auction-filter-active-text');
                nameEl.classList.remove('title-clr', 'textclr');
                li.classList.add('auction-filter-active-item');
            } else {
                nameEl.classList.remove('auction-filter-active-text');
                nameEl.classList.add('title-clr', 'textclr');
                li.classList.remove('auction-filter-active-item');
            }
        });

        if (typeof window.auctionRenderProductsList === 'function') {
            window.auctionRenderProductsList();
            return;
        }
        form.submit();
    }
</script>
