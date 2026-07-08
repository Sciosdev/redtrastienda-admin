<div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
    <div class="row g-sm-3 g-2">
        @forelse($products as $auctionProduct)
            <div class="col-sm-6 col-lg-4 col-xl-3">
                @include('auction.web-views.partials._auction-product-card', ['auctionProduct' => $auctionProduct])
            </div>
        @empty
            <div class="col-12 d-flex justify-content-center align-items-center w-100 py-5">
                <div class="text-center">
                    <img width="66" height="80" src="{{ dynamicAsset(path: 'public/assets/front-end/img/media/product.svg') }}" class="img-fluid mb-1" alt="">
                    <h6 class="text-muted">{{ translate('no_product_found') }}</h6>
                </div>
            </div>
        @endforelse
    </div>
</div>

<div class="tab-pane grid-list__view fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
    <div class="row g-sm-3 g-2">
        @forelse($products as $auctionProduct)
            <div class="col-md-6">
                @include('auction.web-views.partials._auction-product-horizontal-thin', ['auctionProduct' => $auctionProduct])
            </div>
        @empty
            <div class="col-12 d-flex justify-content-center align-items-center w-100 py-5">
                <div class="text-center">
                    <img width="66" height="80" src="{{ dynamicAsset(path: 'public/assets/front-end/img/media/product.svg') }}" class="img-fluid mb-1" alt="">
                    <h6 class="text-muted">{{ translate('no_product_found') }}</h6>
                </div>
            </div>
        @endforelse
    </div>
</div>

@if($products->hasPages())
    <div class="mt-3" id="paginator-ajax">
        {!! $products->links() !!}
    </div>
@endif
