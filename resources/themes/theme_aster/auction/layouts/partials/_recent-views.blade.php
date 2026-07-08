@if(isset($auctionRecentView) && count($auctionRecentView) > 0)
<div class="offcanvas dark-support_offcanvas offcanvas {{ Session::get('direction') === 'rtl' ? 'offcanvas-start' : 'offcanvas-end' }}" tabindex="-1" id="recently_view-offcanvas"
     aria-labelledby="recently_view-offcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h6 class="offcanvas-title fw-semibold m-0">
            {{ translate('Recently Viewed Auctions') }}
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
        <div class="d-flex flex-column gap-3">
            @foreach($auctionRecentView as $auctionProduct)
                @include('auction.web-views.partials._auction-recent-view-horizontal', ['auctionProduct' => $auctionProduct])
            @endforeach
        </div>
    </div>
</div>
@endif
