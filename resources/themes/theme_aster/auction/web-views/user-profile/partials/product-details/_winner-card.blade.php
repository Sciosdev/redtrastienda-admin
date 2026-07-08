<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex gap-3 align-items-center justify-content-between">
            <div class="d-flex gap-10px align-items-center">
                <div class="rounded w-50px h-50px min-w-50px overflow-hidden text-center rounded-circle">
                    <img src="{{ $claimWinnerImage }}" alt="{{ $claimWinnerName }}" class="w-100 h-100 object-cover">
                </div>
                <div>
                    <h6 class="mb-1 d-flex align-items-center gap-1 fs-14 text-capitalize fw-semibold title-clr line--limit-1">
                        {{ $claimWinnerName }}
                        <img width="16" height="16" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/winning-badge.svg') }}" alt="">
                    </h6>
                    <p class="m-0 fs-13 title-semidark">{{ translate('Auction Claimer') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
