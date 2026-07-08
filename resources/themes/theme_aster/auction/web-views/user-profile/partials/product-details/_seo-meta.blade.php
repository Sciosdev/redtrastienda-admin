@if($isOwner && $auctionProduct->seo)
<div class="border-top pt-3 mt-3">
    <h4 class="fs-16 mb-3 title-clr fw-semibold">{{ translate('Product SEO & Meta data') }}</h4>
    <div class="mb-3">
        <h5 class="fs-14 mb-1 title-clr fw-semibold">
            {{ $auctionProduct->seo->title ?? translate('No_SEO_Title_Available') }}
        </h5>
        <p class="fs-14 mb-0 title-semidark fw-normal">
            {{ $auctionProduct->seo->description ?? translate('No_SEO_Description_Available') }}
        </p>
    </div>
    @if($metaImageUrl)
        <div class="row g-3">
            <div class="col-lg-4 col-md-4 col-6">
                <div class="position-relative h-100px w-100 bs-border rounded overflow-hidden">
                    <div class="custom-image-popup-init">
                        <a href="{{ $metaImageUrl }}" class="custom-image-popup">
                            <img src="{{ $metaImageUrl }}" alt="{{ $auctionProduct->seo->title }}"
                                 class="object-cover-center">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endif
