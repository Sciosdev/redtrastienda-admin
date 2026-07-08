<div class="p-15px card border-0 shadow-sm rounded">
    <div class="fs-14 fw-semibold title-clr mb-3">{{ translate('Auction Duration') }}</div>
    <div class="d-flex flex-column gap-15px">
        <div class="d-flex align-items-center gap-2 justify-content-between w-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="minmax-xs-100px fs-14 title-semidark">{{ translate('Start Date') }}</div>
                <span>:</span>
            </div>
            <div class="info-option2">
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ $auctionProduct?->start_time?->format('d M Y, h:i A') ?? translate('N/A') }}</h4>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 justify-content-between w-100">
            <div class="d-flex align-items-center justify-content-between">
                <div class="minmax-xs-100px fs-14 title-semidark">{{ translate('End Date') }}</div>
                <span>:</span>
            </div>
            <div class="info-option2">
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ $auctionProduct?->end_time?->format('d M Y, h:i A') ?? translate('N/A') }}</h4>
            </div>
        </div>
    </div>
</div>
