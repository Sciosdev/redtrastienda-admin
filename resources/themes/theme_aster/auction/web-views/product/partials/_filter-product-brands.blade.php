<div class="">
    <h6 class="fs-13 fw-semibold mb-15">{{ translate('Brands') }}</h6>
    <div class="w-100 form-auction overflow-hidden rounded border d-flex align-items-center position-relative mb-15">
        <input id="auction-brand-search-input" class="form-control min-h-40 rounded-0 border-0" type="text" placeholder="{{ translate('Search_by_name') }}" name="" value="" autocomplete="off">
        <button class="btn btn-primary bg-white text-primary border-0 min-h-40 w-50px rounded-0 d-center" type="button">
            <i class="fi fi-rr-search"></i>
        </button>
    </div>
    <ul id="auction-brand-filter-list" class="for-brand webkit-scrolling-custom d-flex flex-column gap-10px list-group">
        @forelse($productBrands ?? [] as $brand)
            <li class="d-flex align-items-center gap-1 justify-content-between cursor--pointer px-2 auction-brand-item {{ request('brand_id') == $brand->id ? 'fw-semibold' : '' }}"
                data-brand-id="{{ $brand->id }}"
                onclick="auctionSetBrandFilter({{ $brand->id }})">
                <div class="text-start fs-13 title-clr textclr line--limit-1">
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
</div>

<script>
    document.getElementById('auction-brand-search-input').addEventListener('keyup', function () {
        var search = this.value.toLowerCase();
        var items  = document.querySelectorAll('#auction-brand-filter-list .auction-brand-item');
        items.forEach(function (li) {
            li.style.display = li.innerText.toLowerCase().indexOf(search) !== -1 ? '' : 'none';
        });
    });

    function auctionSetBrandFilter(brandId) {
        var form = document.querySelector('.product-list-filter');
        if (!form) return;

        var brandInput = form.querySelector('input[name="brand_id"]');
        var dataFrom   = form.querySelector('input[name="data_from"]');
        var catInput   = form.querySelector('input[name="category_id"]');

        if (brandInput) brandInput.value = (brandInput.value == brandId) ? '' : brandId;

        // Mutual exclusivity: brand and category cannot coexist
        if (catInput) catInput.value = '';

        if (dataFrom) dataFrom.value = (brandInput && brandInput.value) ? 'brand' : '';

        var activeBrandId = brandInput && brandInput.value ? String(brandInput.value).trim() : '';
        document.querySelectorAll('.auction-brand-item').forEach(function (li) {
            var isActive = activeBrandId !== '' && String(li.getAttribute('data-brand-id')).trim() === activeBrandId;
            li.classList.toggle('fw-semibold', isActive);
        });

        // Clear category active states
        document.querySelectorAll('a[data-category-id]').forEach(function (a) {
            a.classList.remove('fw-semibold', 'text-primary');
            a.classList.add('title-clr');
        });

        var pageInput = form.querySelector('input[name="page"]');
        if (pageInput) pageInput.value = 1;

        if (typeof window.auctionRenderProductsList === 'function') {
            window.auctionRenderProductsList({resetPage: true});
            return;
        }

        form.submit();
    }
</script>
