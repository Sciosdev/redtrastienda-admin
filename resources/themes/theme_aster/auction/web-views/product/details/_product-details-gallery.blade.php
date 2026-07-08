<div class="pd-img-wrap position-relative">
    <div class="product-slider">
        <div class="owl-carousel owl-theme big-slider">
            @foreach($imageSources as $photo)
                @php
                    $imagePath = isset($photo['image_name'])
                            ? getStorageImages(path: $photo['image_name'], type: 'backend-product')
                            : getStorageImages(path: $photo, type: 'backend-product');
                @endphp
                <div class="item">
                    <div class="position-relative me-1">
                        <div class="product-thumb-big overflow-hidden rounded">
                            <div class="easyzoom easyzoom--overlay overflow-hidden">
                                <a href="{{ $imagePath }}">
                                    <img src="{{ $imagePath }}" alt="{{ $auctionProduct->name }}"
                                         class="w-100 object-cover h-100 rounded">
                                </a>
                            </div>
                        </div>
                        <div class="m-lg-3 m-2 d-flex social-badge gap-10px z-99 position-absolute top-0 inline-end-0">
                            <div class="bg-white d-center rounded-circle w-35px h-35px min-w-35px border"
                                 data-bs-placement="left" data-bs-toggle="tooltip"
                                 data-bs-title="{{ $productTypeLabel }}">
                                <img width="14" height="14" src="{{ $productTypeIcon }}" alt="{{ $productTypeLabel }}"
                                     class="">
                            </div>
                            <div class="share_dropdown_wrapper">
                                <button type="button"
                                        class="btn d-center btn-outline-primary rounded-circle w-35px h-35px min-w-35px p-0 share_btn"
                                        style="--size: 35px" tabindex="0">
                                    <i class="fi fi-sr-share fs-14"></i>
                                </button>
                                <div class="share_dropdown bg-white d-flex gap-2 align-items-center flex-column">
                                    <a href="#" class="bg-facebook share-on-social-media facebook d-center"
                                       data-action="{{ $productDetailUrl }}"
                                       data-social-media-name="facebook.com/sharer/sharer.php?u=">
                                        <i class="fi fi-brands-facebook"></i>
                                    </a>
                                    <a href="#" class="bg-twitter share-on-social-media twitter d-center"
                                       data-action="{{ $productDetailUrl }}"
                                       data-social-media-name="twitter.com/intent/tweet?text=">
                                        <i class="fi fi-brands-twitter-alt"></i>
                                    </a>
                                    <a href="#" class="bg-linkedin share-on-social-media linkedin d-center"
                                       data-action="{{ $productDetailUrl }}"
                                       data-social-media-name="linkedin.com/shareArticle?mini=true&url=">
                                        <i class="fi fi-brands-linkedin"></i>
                                    </a>
                                    <a href="#" class="bg-whatsapp share-on-social-media whatsapp d-center"
                                       data-action="{{ $productDetailUrl }}"
                                       data-social-media-name="api.whatsapp.com/send?text=">
                                        <i class="fi fi-brands-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="thumb-slider-wrap d-flex align-items-center gap-2 mt-2">
            <button type="button" class="thumb-nav-btn thumb-prev btn btn-outline-primary rounded-circle p-0 d-center flex-shrink-0">
                <i class="fi fi-rr-angle-left fs-12"></i>
            </button>
            <div class="owl-carousel owl-theme thumb-slider flex-grow-1 overflow-hidden">
                @foreach($imageSources as $photo)
                    @php
                        $imagePath = isset($photo['image_name'])
                            ? getStorageImages(path: $photo['image_name'], type: 'backend-product')
                            : getStorageImages(path: $photo, type: 'backend-product');
                    @endphp
                    <div class="item">
                        <div class="product-thumb-small overflow-hidden rounded">
                            <img src="{{ $imagePath }}" alt="{{ $auctionProduct->name }}"
                                 class="w-100 object-cover h-100 rounded">
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="thumb-nav-btn thumb-next btn btn-outline-primary rounded-circle p-0 d-center flex-shrink-0">
                <i class="fi fi-rr-angle-right fs-12"></i>
            </button>
        </div>
    </div>
</div>
