<div class="details-content-wrap show-more--content d-flex flex-column gap-15px custom-height active overflow-hidden">
    @if($auctionProduct['details'])
        <div class="p-details-description">
            <h4 class="fs-16 fw-semibold mb-3 bg-light rounded py-2 px-3">
                {{ translate('Detail Description') }} 
            </h4>
            <p class="fs-14 pragraph-clr m-0">
                {!! $auctionProduct['details'] !!}
            </p>
        </div>
    @endif
</div>
<div class="text-center mt-3">
    <button type="button" class="see-more-details btn px-3 py-2 btn-outline-primary mx-auto">
        {{ translate('See More') }}
    </button>
</div>
