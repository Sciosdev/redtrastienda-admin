@extends("auction.layouts.auction-app")

@section('title', translate('Auction Product Create'))

@section('content')
<div class="py-4">
    <div class="container">
        <div class="">
            <div class="row g-3">
                @include("auction.web-views.partials._auction-profile-sidebar")
                <div class="col-lg-9">
                    <div class="d-flex flex-wrap gap-3 align-items-center mb-20 justify-content-between">
                        <h3 class="fs-18 title-clr mb-0 fw-semibold flex-grow-1">
                            {{ translate('Auction Product Create') }}
                        </h3>
                        @if(getActiveAIProviderConfigCache())
                        <div class="bg-white shadow-sm rounded-pill d-inline-flex align-items-center gap-2 px-3 py-2 text-nowrap" id="ai-remaining-count">
                            <span class="fw-bold title-clr" id="count">{{ $aiRemainingCount ?? 0 }}</span>
                            <span class="title-semidark fs-14">{{ translate('generates_left') }}</span>
                            <img width="18" height="18" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/hexa-anim.svg') }}" alt="">
                        </div>
                        @endif
                    </div>
                    <form class="product-form text-start" id="auction_product_form" method="POST" action="{{ route('auction.acution-create-product.add') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card bs-border shadow-sm card-body">
                            <div class="mb-20">
                                <h3 class="mb-1 fs-18 fw-semibold">{{ translate('Basic Setup') }}</h3>
                                <p class="fs-12 title-semidark mb-0">
                                    {{ translate('Here you can setup the auction product information') }}
                                </p>
                            </div>
                            <div class="row g-4">
                                <div class="col-lg-8 col-xxl-7">
                                    <div class="light-box rounded-10 p-xxl-20px p-3">
                                        <div class="position-relative mb-3">
                                            <ul class="nav nav--border text-capitalize lang_tab" id="pills-tab"
                                                role="tablist">
                                                @foreach ($languages as $lang)
                                                    <li class="nav-item py-0" role="presentation">
                                                        <a class="nav-link {{ $lang == $defaultLanguage ? 'active' : '' }}"
                                                            id="{{ $lang }}-link" data-bs-toggle="pill"
                                                            href="#{{ $lang }}-form" role="tab"
                                                            aria-controls="{{ $lang }}-form"
                                                            aria-selected="{{ $lang == $defaultLanguage ? 'true' : 'false' }}">
                                                            {{ getLanguageName($lang) . '(' . strtoupper($lang) . ')' }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="tab-content" id="pills-tabContent">
                                            @foreach ($languages as $lang)
                                                <div class="tab-pane fade {{ $lang == $defaultLanguage ? 'show active' : '' }}"
                                                    id="{{ $lang }}-form" role="tabpanel"
                                                    aria-labelledby="{{ $lang }}-link">
                                                    <div class="form-group mb-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-10px">
                                                            <label class="form-label mb-0" for="{{ getLanguageCode($lang) }}_name">
                                                                {{ translate('Product_Name') }}
                                                                ({{ strtoupper($lang) }})
                                                                @if($lang == $defaultLanguage)
                                                                    <span class="input-required-icon text-danger">*</span>
                                                                @endif
                                                            </label>
                                                            @if(getActiveAIProviderConfigCache())
                                                            <button type="button"
                                                                class="btn d-flex fs-14 align-items-center gap-1 text-primary shadow-none border-0 opacity-1 generate_btn_wrapper p-0 auction-basic-setup-title-generate flex-nowrap"
                                                                id="auction-title-{{ getLanguageCode($lang) }}-action-btn"
                                                                data-lang="{{ getLanguageCode($lang) }}"
                                                                data-route="{{ route('customer.auction.product.title-auto-fill') }}">
                                                                <div class="btn-svg-wrapper">
                                                                   <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/hexa-anim.svg') }}" alt="" class="">
                                                                </div>
                                                                <span class="ai-text-animation d-none" role="status">
                                                                    {{ translate('Just_a_second') }}
                                                                </span>
                                                                <span class="btn-text">{{ translate('Generate') }}</span>
                                                            </button>
                                                            @endif
                                                        </div>
                                                        <div class="outline-wrapper" id="auction-title-container-{{ getLanguageCode($lang) }}">
                                                            <input type="text" {{ $lang == $defaultLanguage ? 'required' : '' }}
                                                                name="name[]" id="{{ getLanguageCode($lang) }}_name"
                                                                class="form-control {{ $lang == $defaultLanguage ? 'product-title-default-language' : '' }}"
                                                                placeholder="{{ translate('Type product name') }}">
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="lang[]" value="{{ $lang }}">

                                                    <div class="form-group mb-0">
                                                        <div class="d-flex justify-content-between align-items-center mb-10px">
                                                            <label class="form-label mb-0" for="description-{{ getLanguageCode($lang) }}">
                                                                {{ translate('description') }}
                                                                ({{ strtoupper($lang) }})
                                                                @if($lang == $defaultLanguage)
                                                                    <span class="input-required-icon text-danger">*</span>
                                                                @endif
                                                            </label>
                                                            @if(getActiveAIProviderConfigCache())
                                                            <button type="button"
                                                                class="btn d-flex fs-14 align-items-center gap-1 text-primary shadow-none border-0 opacity-1 generate_btn_wrapper p-0 auction-basic-setup-description-generate flex-nowrap"
                                                                id="auction-description-{{ getLanguageCode($lang) }}-action-btn"
                                                                data-lang="{{ getLanguageCode($lang) }}"
                                                                data-route="{{ route('customer.auction.product.description-auto-fill') }}">
                                                                <div class="btn-svg-wrapper">
                                                                   <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/hexa-anim.svg') }}" alt="" class="">
                                                                </div>
                                                                <span class="ai-text-animation d-none" role="status">
                                                                    {{ translate('Just_a_second') }}
                                                                </span>
                                                                <span class="btn-text">{{ translate('Generate') }}</span>
                                                            </button>
                                                            @endif
                                                        </div>
                                                        <div class="outline-wrapper" id="auction-description-container-{{ getLanguageCode($lang) }}">
                                                            <div id="description-{{ getLanguageCode($lang) }}-editor" class="quill-editor editor-min-h-80"></div>
                                                            <textarea name="description[]" id="description-{{ getLanguageCode($lang) }}"
                                                                class="{{ $lang == $defaultLanguage ? 'product-description-default-language' : '' }} d-none"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-xxl-5">
                                    <div class="h-100 light-box rounded-10 p-20">
                                        <div class="">
                                            <div class="mb-5">
                                                <label for="" class="form-label fs-14 fw-semibold mb-1">
                                                    {{ translate('Product thumbnail') }}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <p class="fs-12 pragraph-clr2 mb-0">{{ translate('Upload image') }}</p>
                                            </div>
                                            <div class="d-flex justify-content-center">
                                                <div class="upload-file">
                                                    <input type="file" name="thumbnail" class="upload-file__input single_file_input action-upload-color-image"
                                                        accept="{{ getFileUploadFormats(skip: '.svg') }}"
                                                        data-max-size="{{ getFileUploadMaxSize() }}"
                                                        value="" data-imgpreview="pre_img_viewer">
                                                    <label class="upload-file__wrapper">
                                                        <div class="upload-file-textbox text-center">
                                                            <img width="27" height="26" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/click-to-upload.png') }}" alt="" class="">
                                                            <h6 class="mt-2 mb-0 fs-10 fw-medium title-semidark lh-base text-center">
                                                                <span class="text-info">{{ translate('Click to upload') }}</span>
                                                                <br>
                                                                {{ translate('or drag and drop') }}
                                                            </h6>
                                                        </div>
                                                        <img class="upload-file-img" loading="lazy" src="" data-default-src="" alt="">
                                                    </label>
                                                    <div class="overlay">
                                                        <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                            <button type="button" class="btn btn-outline-primary icon-btn view_btn d-center">
                                                                <i class="fi fi-sr-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-primary icon-btn edit_btn d-center">
                                                                <i class="fi fi-rr-camera"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="fs-10 mb-0 title-semidark text-center mt-20">
                                               {{ translate('JPG, JPEG, PNG Image size : Max 2 MB') }}
                                                <span class="title-clr fw-medium">
                                                    (1:1)
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="general_wrapper mt-3">
                            <div class="outline-wrapper auction-general-setup-section">
                                <div class="card bs-border shadow-sm">
                                    <div class="card-body">
                                        <div class="mb-0">
                                            <div class="card-header gap-1 mb-20 d-flex flex-sm-nowrap flex-wrap justify-content-between border-0 p-0 bg-transparent">
                                                <div class="">
                                                    <h3 class="mb-1 fs-18 fw-semibold">{{ translate('General Setup') }}</h3>
                                                    <p class="fs-12 title-semidark mb-0">
                                                        {{ translate('Here you can Set up the foundational details required for auction product creation.') }}
                                                    </p>
                                                </div>
                                                @if(getActiveAIProviderConfigCache())
                                                <button type="button"
                                                    class="btn d-flex fs-14 align-items-center gap-1 text-primary shadow-none border-0 opacity-1 generate_btn_wrapper p-0 auction-general-setup-generate flex-nowrap"
                                                    data-route="{{ route('customer.auction.product.general-setup-auto-fill') }}">
                                                    <div class="btn-svg-wrapper">
                                                        <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/hexa-anim.svg') }}" alt="" class="">
                                                    </div>
                                                    <span class="ai-text-animation d-none" role="status">
                                                        {{ translate('Just_a_second') }}
                                                    </span>
                                                    <span class="btn-text">{{ translate('Generate') }}</span>
                                                </button>
                                                @endif
                                            </div>
                                            <div class="light-box rounded-3 p-xxl-20px p-3 ">
                                                <div class="row g-3 g-md-3">
                                                    <div class="col-md-6 col-lg-4">
                                                        <label class="form-label">{{ translate('Category') }} <span class="text-danger">*</span></label>
                                                        <div class="select-wrappers">
                                                            <select class="form-select bs-border min-h-40 fs-14" name="category_id" id="category_id" required>
                                                                <option value="" disabled selected>{{ translate('Select category') }}</option>
                                                                @foreach($categories as $category)
                                                                    <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-lg-4">
                                                        <label class="form-label">{{ translate('Brands') }}</label>
                                                        <div class="select-wrappers">
                                                            <select class="form-select bs-border min-h-40 fs-14" name="brand_id" id="brand_id">
                                                                <option value="" disabled selected>{{ translate('Select Brands') }}</option>
                                                                <option value="{{ null }}">
                                                                    {{ translate('No_Brand') }}
                                                                </option>
                                                                @foreach($brands as $brand)
                                                                    <option value="{{ $brand['id'] }}">{{ $brand['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-lg-4">
                                                        <label class="form-label">{{ translate('Item_Condition') }} <span class="text-danger">*</span></label>
                                                        <div class="select-wrappers">
                                                            <select name="item_condition" id="item_condition" class="form-select bs-border min-h-40 fs-14" required>
                                                                <option value="" disabled selected>{{ translate('Select_Item_Condition') }}</option>
                                                                @foreach(\Modules\Auction\app\Enums\ItemCondition::ALL as $condition)
                                                                    <option value="{{ $condition }}">{{ translate($condition) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="general_wrapper mt-3">
                            <div class="outline-wrapper auction-shipping-policy-section">
                                <div class="card bs-border shadow-sm">
                                    <div class="card-body">
                                        <div class="mb-0">
                                            <div class="card-header gap-1 mb-20 d-flex flex-sm-nowrap flex-wrap justify-content-between border-0 p-0 bg-transparent">
                                                <div class="">
                                                    <h3 class="mb-1 fs-18 fw-semibold">{{ translate('Shipping Charge & Return Policy') }}</h3>
                                                    <p class="fs-12 title-semidark mb-0">
                                                        {{ translate('The auction owner is responsible for shipping, delivery issues, and handling all return requests.') }}
                                                    </p>
                                                </div>
                                                @if(getActiveAIProviderConfigCache())
                                                <button type="button"
                                                    class="btn d-flex fs-14 align-items-center gap-1 text-primary shadow-none border-0 opacity-1 generate_btn_wrapper p-0 auction-shipping-policy-generate flex-nowrap"
                                                    data-route="{{ route('customer.auction.product.shipping-policy-auto-fill') }}">
                                                    <div class="btn-svg-wrapper">
                                                        <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/hexa-anim.svg') }}" alt="" class="">
                                                    </div>
                                                    <span class="ai-text-animation d-none" role="status">
                                                        {{ translate('Just_a_second') }}
                                                    </span>
                                                    <span class="btn-text">{{ translate('Generate') }}</span>
                                                </button>
                                                @endif
                                            </div>
                                            <div class="light-box rounded-3 p-xxl-20px p-3 ">
                                                <div class="row g-3 g-md-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label mb-10px">
                                                            <h5 class="d-flex fs-14 align-items-center mb-1 gap-1">
                                                                {{ translate('Shipping Fee') }}
                                                                <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ translate('Enter_the_shipping_fee_you_will_charge_the_buyer_for_delivering_this_item.') }}">
                                                                    <i class="fi fi-sr-info text-light-gray fs-12"></i>
                                                                </span>
                                                            </h5>
                                                            <p class="mb-0 fs-12">{{ translate('If you want to charge a shipping fee, enter the amount here.') }}</p>
                                                        </label>
                                                        <input type="number" min="0" step="any" name="shipping_fee" id="shipping_fee" placeholder="{{ translate('Ex : 10') }}" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label mb-10px">
                                                            <h5 class="d-flex fs-14 align-items-center mb-1 gap-1">
                                                                {{ translate('Return Policy') }}
                                                                <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ translate('Describe_the_conditions_under_which_you_accept_returns_for_this_item.') }}">
                                                                    <i class="fi fi-sr-info text-light-gray fs-12"></i>
                                                                </span>
                                                            </h5>
                                                            <p class="mb-0 fs-12">{{ translate('If you want to allow returns, define the return policy here.') }}</p>
                                                        </label>
                                                        <input type="text" name="return_policy" id="return_policy" placeholder="{{ translate('Type Return policy') }}  - {{ translate('Max_255_characters') }}" maxlength="255" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row product-image-wrapper g-3 mt-0">
                            <div class="additional-image-column-section col-md-12">
                                <div class="card shadow-sm bs-border card-body h-100">
                                    <div class="mb-20">
                                        <h3 class="mb-1 fs-18 fw-semibold">{{ translate('Product Additional Images') }}</h3>
                                        <p class="fs-12 title-semidark mb-0">
                                            {{ translate('Upload additional images for this product from here.') }} <span class="text-info">JPG, JPEG, PNG Image size : Max 2 MB (1:1)</span>
                                        </p>
                                    </div>
                                    <div class="d-flex flex-column light-box rounded-3" id="additional_Image_Section">
                                        <div class="position-relative">
                                            <div class="multi_image_picker d-flex gap-3 p-xxl-20px p-3"
                                                data-ratio="1/1"
                                                data-max-filesize="{{getFileUploadMaxSize()}}"
                                                data-field-name="images[]"
                                                data-required="true"
                                                data-required-msg="{{ translate('additional_image_is_required') }}"
                                                data-allowed-formats="{{ getFileUploadFormats(skip: '.svg,.gif') }}"
                                                data-validation-error-msg="{{ translate('File_size_is_too_large_Maximum_').' '.getFileUploadMaxSize().' '.'MB' }}"
                                            >
                                                <div>
                                                    <div class="imageSlide_prev">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center h-100">
                                                            <button
                                                                type="button"
                                                                class="btn btn-primary p-2 rounded-circle border-0">
                                                                <i class="fi fi-sr-angle-left fs-12"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="imageSlide_next">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center h-100">
                                                            <button
                                                                type="button"
                                                                class="btn btn-primary p-2 rounded-circle border-0">
                                                                <i class="fi fi-sr-angle-right fs-12"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bs-border shadow-sm card-body mt-3">
                            <div class="mb-4">
                                <h3 class="mb-1 fs-18 fw-semibold">{{ translate('Product video') }}</h3>
                                <p class="fs-12 title-semidark mb-0">
                                    {{ translate('Add the Auction video File or link here.') }}
                                </p>
                            </div>

                            <div class="d-flex gap-2 flex-column flex-sm-row mb-3">
                                <div class="form-check d-flex gap-2 lh-1">
                                    <input class="form-check-input radio--input mt-0" type="radio" name="video_provider" id="video-link" value="youtube_link" checked>

                                    <label class="form-check-label font-change mb-0" for="video-link">{{ translate('Upload_Video_Link') }}</label>
                                </div>
                                <div class="form-check d-flex gap-2 lh-1 ms-sm-5">
                                    <input class="form-check-input radio--input mt-0" type="radio" name="video_provider" id="video-file" value="custom_video">
                                    <label class="form-check-label font-change mb-0" for="video-file">{{ translate('Upload_Video_File') }}</label>
                                </div>
                            </div>

                            <div class="light-box rounded-10 p-3 youtube-link-here">
                                <div class="mb-2">
                                    <label class="form-label title-clr fs-14 mb-0">
                                        {{ translate('Youtube_video_link') }}
                                    </label>
                                    <span class="fs-14 text-light-gray">({{ translate('Optional') }})</span>
                                </div>
                                <input type="text" name="youtube_video_url"  placeholder="Ex: https://www.youtube.com/embed/5R06LRdUCSE" class="form-control border min-h-40">
                                <p class="mt-1 fs-14 text-light-gray mb-0">{{ translate('Add_the_YouTube_video_link_here._Only_the_YouTube-embedded_link_is_supported') }}.</p>
                            </div>
                            <div class="light-box rounded-10 p-3 upload-video-file-here d--none">
                                <div class="image-uploader overflow-visible" style="--size: 100px;"
                                    data-mp4-icon="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/video-icon.svg') }}"
                                    data-default-icon="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/video-icon.svg') }}">
                                    <input type="file" name="custom_video_url" class="image-uploader__zip"
                                        id="input-file" accept=".mp4,.webm" data-max-size="{{ getFileUploadMaxSize(type: 'file') }}">
                                    <div class="image-uploader__zip-preview gap-1 overflow-hidden">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/video-icon.svg') }}"
                                            class="mx-auto upload-preview-icon h-30" width="28" alt="">
                                        <div
                                            class="image-uploader__title fs-9 fw-medium text-info overflow-wrap-anywhere line--limit-2">
                                            {{ translate('Click_to_upload') }}
                                            <br>
                                            <span class="text-body">{{ translate('or_drag_and_drop') }}</span>
                                        </div>
                                    </div>

                                    <span class="btn btn-danger p-1 rounded-circle collapse zip-remove-btn zip-remove-btn__outside">
                                        <div class="d-flex justify-content-center align-items-center h-100">
                                            <i class="fi fi-rr-cross-small d-flex"></i>
                                        </div>
                                    </span>

                                </div>
                                <p class="mt-2 fs-12 text-light-gray mb-0">{{ translate('Supported_video_formats_MP4_and_WEBM') }}. {{ translate('Maximum_size') }}: {{ getFileUploadMaxSize(type: 'file') }} {{ translate('MB') }}.</p>
                            </div>
                        </div>

                        <div class="price_wrapper mt-3">
                            <div class="outline-wrapper auction-info-section">
                                <div class="card bs-border">
                                    <div class="card-body">
                                        <div class="card-header gap-1 mb-20 d-flex flex-sm-nowrap flex-wrap justify-content-between border-0 p-0 bg-transparent">
                                            <div class="">
                                                <h3 class="mb-1 fs-18 fw-semibold">{{ translate('Auction Info ') }}</h3>
                                                <p class="fs-12 title-semidark mb-0">
                                                    {{ translate('Here you can setup the auction information for the product.') }}
                                                </p>
                                            </div>
                                            @if(getActiveAIProviderConfigCache())
                                            <button type="button"
                                                class="btn d-flex fs-14 align-items-center gap-1 text-primary shadow-none border-0 opacity-1 generate_btn_wrapper p-0 auction-info-generate flex-nowrap"
                                                data-route="{{ route('customer.auction.product.auction-info-auto-fill') }}">
                                                <div class="btn-svg-wrapper">
                                                    <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/hexa-anim.svg') }}" alt="" class="">
                                                </div>
                                                <span class="ai-text-animation d-none" role="status">
                                                    {{ translate('Just_a_second') }}
                                                </span>
                                                <span class="btn-text">{{ translate('Generate') }}</span>
                                            </button>
                                            @endif
                                        </div>
                                        <div class="light-box rounded-10 p-xxl-20px p-3">
                                            <div class="row gy-4 align-items-end">
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="form-group">
                                                        <label class="form-label fs-14 d-flex align-items-center gap-1">
                                                            {{ translate('Start Price') }}
                                                            <span class="input-required-icon text-danger">*</span>
                                                            ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                                            <span class="tooltip-icon cursor-pointer"
                                                                data-bs-toggle="tooltip"
                                                                aria-label="{{ translate('Set the starting price for this product. This start price would not be applied if you set a variation-wise price.') }}"
                                                                data-bs-title="{{ translate('Set the starting price for this product. This start price would not be applied if you set a variation-wise price.') }}">
                                                                <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                            </span>
                                                        </label>

                                                        <input type="number" min="0" step="any"
                                                            placeholder="{{ translate('Start Price') }}" name="starting_price" id="starting_price"
                                                            value="" class="form-control min-h-40"
                                                            data-required-msg="{{ translate('Start_price_is_required') }}" required="">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-lg-4" id="">
                                                    <div class="form-group">
                                                        <label class="form-label fs-14 d-flex align-items-center gap-1" for="minimum_increment_amount">
                                                            {{ translate('Minimum Increment') }}
                                                            <span class="input-required-icon text-danger">*</span>
                                                            ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                                            <span class="tooltip-icon cursor-pointer"
                                                                data-bs-toggle="tooltip"
                                                                aria-label="{{ translate('Each new bid must be at least the current highest bid plus this minimum increment amount.') }}"
                                                                data-bs-title="{{ translate('Each new bid must be at least the current highest bid plus this minimum increment amount.') }}">
                                                                <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                            </span>
                                                        </label>
                                                        <input type="number" min="0" step="any" value="1"
                                                            placeholder="{{ translate('Ex : 10') }}"
                                                            name="minimum_increment_amount" id="minimum_increment_amount"
                                                            class="form-control min-h-40 only-number-input">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-lg-4" id="">
                                                    <div class="form-group">
                                                        <label class="form-label fs-14 d-flex align-items-center gap-1" for="maximum_decrement_amount">
                                                            {{ translate('Maximum_Decrement') }}
                                                            <span class="input-required-icon text-danger">*</span>
                                                            ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                                            <span class="tooltip-icon cursor-pointer"
                                                                data-bs-toggle="tooltip"
                                                                aria-label="{{ translate('Set_the_maximum_decrement_amount_for_bidding_on_this_product._Otherwise,_the_checkout_process_would_not_start') }}"
                                                                data-bs-title="{{ translate('Set_the_maximum_decrement_amount_for_bidding_on_this_product._Otherwise,_the_checkout_process_would_not_start') }}">
                                                                <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                            </span>
                                                        </label>

                                                        <input type="number" min="0" step="any" value="1"
                                                            placeholder="{{ translate('Ex : 10') }}" name="maximum_decrement_amount"
                                                            id="maximum_decrement_amount" class="form-control min-h-40 only-number-input"
                                                            data-required-msg="{{ translate('Maximum_decrement_is_required') }}" required="">
                                                    </div>
                                                </div>
                                                @if ($productWiseTax)
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="form-group">
                                                            <label class="form-label fs-14 d-flex align-items-center gap-1">
                                                                {{ translate('Select_Vat/Tax_Rate') }}
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <div class="select-wrappers">
                                                                <select class=" w-100 multiple-select2" multiple="multiple" name="tax_ids[]" id="tax_ids" data-placeholder="{{ translate('type_&_select_vat_rate') }}">
                                                                    @foreach($taxVats as $taxVat)
                                                                        <option value="{{ $taxVat->id }}" {{ in_array($taxVat->id, $taxVatIds ?? [], true) ? 'selected' : '' }}>
                                                                            {{ $taxVat->name }} ({{ $taxVat->tax_rate }}%)
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="form-group">
                                                        <label class="form-label fs-14 d-flex align-items-center gap-1">
                                                            {{ translate('Auction Start Time') }}
                                                            <span class="text-danger">*</span>
                                                            <span class="tooltip-icon cursor-pointer"
                                                                data-bs-toggle="tooltip" data-bs-title="{{ translate('The date and time when this auction becomes active and starts accepting bids from buyers.') }}">
                                                                <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                            </span>
                                                        </label>
                                                        <div class="position-relative">
                                                            <span class="fi fi-sr-calendar icon-absolute-on-right"></span>
                                                            <input type="text" name="auction_start_time" id="auction_start_time" class="form-control js-auction-datetime min-h-40" placeholder="{{ translate('Start_date') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="form-group">
                                                        <label class="form-label fs-14 d-flex align-items-center gap-1">
                                                            {{ translate('Auction End Time') }}
                                                            <span class="text-danger">*</span>
                                                            <span class="tooltip-icon cursor-pointer"
                                                                data-bs-toggle="tooltip" data-bs-title="{{ translate('The date and time when this auction closes. No bids will be accepted after this moment.') }}">
                                                                <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                            </span>
                                                        </label>
                                                        <div class="position-relative">
                                                            <span class="fi fi-sr-calendar icon-absolute-on-right"></span>
                                                            <input type="text" name="auction_end_time" id="auction_end_time" class="form-control js-auction-datetime min-h-40" placeholder="{{ translate('End_date') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="form-label fs-14 d-flex align-items-center gap-1">
                                                            {{ translate('Search Tags') }}
                                                            <span class="tooltip-icon cursor-pointer"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-title="{{ translate('add_the_product_search_tag_for_this_product_that_customers_can_use_to_search_quickly') }}">
                                                                <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                            </span>
                                                        </label>
                                                        <input type="text" name="tags" id="tags"
                                                            class="form-control min-h-40" data-role="tagsinput"
                                                            placeholder="{{ translate('Separated_by_commas') }}">
                                                        <small class="text-muted">{{ translate('Separate_multiple_tags_with_commas') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="seo_wrapper mt-3">
                            <div class="outline-wrapper">
                                <div class="card bs-border shadow-sm">
                                    <div class="card-body">
                                        <div class="card-header gap-1 mb-20 d-flex flex-sm-nowrap flex-wrap justify-content-between border-0 p-0 bg-transparent">
                                            <div class="">
                                                <h3 class="mb-1 fs-18 fw-semibold">{{ translate('SEO section ') }}</h3>
                                                <p class="fs-12 title-semidark mb-0">
                                                    {{ translate('Add meta titles descriptions and images for products, This will help more people to find them
                                                    on search engines and see the right details while sharing on other social platforms') }}
                                                </p>
                                            </div>
                                            <div class="min-w-120 d-flex justify-content-sm-end">
                                                @if(getActiveAIProviderConfigCache())
                                                <button type="button"
                                                    class="btn d-flex fs-14 align-items-center gap-1 text-primary shadow-none border-0 opacity-1 generate_btn_wrapper p-0 auction-seo-generate flex-nowrap"
                                                    data-route="{{ route('customer.auction.product.seo-section-auto-fill') }}">
                                                    <div class="btn-svg-wrapper">
                                                        <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/hexa-anim.svg') }}" alt="" class="">
                                                    </div>
                                                    <span class="ai-text-animation d-none" role="status">
                                                        {{ translate('Just_a_second') }}
                                                    </span>
                                                    <span class="btn-text">{{ translate('Generate') }}</span>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row g-4 auction-seo-section">
                                            <div class="col-md-8">
                                                <div class="light-box rounded-10 p-20">
                                                    <div class="position-relative mb-3">
                                                        <ul class="nav nav--border text-capitalize lang_tab" id="pills-tab"
                                                            role="tablist">
                                                            <li class="nav-item py-0" role="presentation">
                                                                <a class="nav-link active" id="en-link" data-bs-toggle="pill"
                                                                    href="#en-form" role="tab" aria-controls="en-form"
                                                                    aria-selected="true">
                                                                    {{ translate('english(EN)') }}
                                                                </a>
                                                            </li>
                                                            <li class="nav-item py-0" role="presentation">
                                                                <a class="nav-link " id="sa-link" data-bs-toggle="pill"
                                                                    href="#sa-form" role="tab" aria-controls="sa-form"
                                                                    aria-selected="false" tabindex="-1">
                                                                    {{ translate('Arabic(SA)') }}
                                                                </a>
                                                            </li>
                                                            <li class="nav-item py-0" role="presentation">
                                                                <a class="nav-link " id="bd-link" data-bs-toggle="pill"
                                                                    href="#bd-form" role="tab" aria-controls="bd-form"
                                                                    aria-selected="false" tabindex="-1">
                                                                    {{ translate('Bangla(BD)') }}
                                                                </a>
                                                            </li>
                                                            <li class="nav-item py-0" role="presentation">
                                                                <a class="nav-link " id="in-link" data-bs-toggle="pill"
                                                                    href="#in-form" role="tab" aria-controls="in-form"
                                                                    aria-selected="false" tabindex="-1">
                                                                    {{ translate('Hindi(IN)') }}
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label class="form-label fs-14 title-clr d-flex align-items-center gap-1">
                                                            {{ translate('Meta Title') }}
                                                            <span class="tooltip-icon cursor-pointer"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-bs-title="{{ translate('Add the auction title, product name, or tagline here. This title will appear on Search Engine Results Pages and when sharing the auction link on social platforms. [ Character Limit : 100 ]') }}">
                                                                <i class="fi fi-sr-info fs-12 text-light-gray"></i>
                                                            </span>
                                                        </label>
                                                        <input type="text" name="meta_title" placeholder="{{ translate('Enter your meta title') }}"
                                                            class="form-control" id="meta_title">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="form-label fs-14 title-clr d-flex align-items-center gap-1">
                                                            {{ translate('Meta Description') }}
                                                            <span class="tooltip-icon cursor-pointer"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-bs-title="{{ translate('Write a short description of this auction listing. This description will appear on search engine results pages and when sharing the auction link on social platforms. [ Character Limit : 160 ]') }}">
                                                                <i class="fi fi-sr-info fs-12 text-light-gray"></i>
                                                            </span>
                                                        </label>
                                                        <textarea rows="4" type="text" name="meta_description"
                                                              placeholder=" {{ translate('Enter your meta description') }}"
                                                            id="meta_description"  class="form-control"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="h-100 light-box rounded-10 p-20">
                                                    <div class="">
                                                        <div class="mb-4 pb-xl-1 text-center">
                                                            <label for="" class="form-label fs-14 fw-semibold mb-1">
                                                                {{ translate('Meta Image') }}
                                                            </label>
                                                            <p class="fs-12 pragraph-clr2 mb-0">{{ translate('Upload image') }}</p>
                                                        </div>
                                                        <div class="d-flex justify-content-center">
                                                            <div class="upload-file">
                                                                <input type="file" name="meta_image" class="upload-file__input single_file_input action-upload-color-image"
                                                                    accept="{{ getFileUploadFormats(skip: '.svg') }}"
                                                                    data-max-size="{{ getFileUploadMaxSize() }}"
                                                                    value="" data-imgpreview="pre_img_viewer">
                                                                <label class="upload-file__wrapper ratio-2-1">
                                                                    <div class="upload-file-textbox text-center">
                                                                        <img width="27" height="26" src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/icons/click-to-upload.png') }}" alt="" class="">
                                                                        <h6 class="mt-2 mb-0 fs-10 fw-medium title-semidark lh-base text-center">
                                                                            <span class="text-info">{{ translate('Click to upload') }}</span>
                                                                            <br>
                                                                            {{ translate('or drag and drop') }}
                                                                        </h6>
                                                                    </div>
                                                                    <img class="upload-file-img" loading="lazy" src="" data-default-src="" alt="">
                                                                </label>
                                                                <div class="overlay">
                                                                    <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                                        <button type="button" class="btn btn-outline-primary icon-btn view_btn d-center">
                                                                            <i class="fi fi-sr-eye"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-outline-primary icon-btn edit_btn d-center">
                                                                            <i class="fi fi-rr-camera"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="fs-10 mb-0 title-semidark text-center mt-20">
                                                        {{ translate('JPG, JPEG, PNG Image size : Max 2 MB') }}
                                                            <span class="title-clr fw-medium">
                                                                (1:1)
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-4 pt-4">
                                            <div class="col-lg-6">
                                                <div class="p-xxl-20px p-2 light-box rounded h-100">
                                                    <div class="bg-white rounded p-2 p-sm-3 mb-3">
                                                        <div class="row g-3">
                                                            <div class="col-sm-6">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <label class="form-check d-flex gap-2">
                                                                        <input type="radio" name="meta_index"
                                                                            value="index" checked=""
                                                                            class="form-check-input radio--input radio--md">
                                                                        <span
                                                                            class="user-select-none fs-14 title-semidark lh-24px form-check-label">{{ translate('Index') }}</span>
                                                                    </label>
                                                                    <span class="tooltip-icon" data-bs-toggle="tooltip"
                                                                        data-bs-title="{{ translate('Allow search engines to put this web page on their list or index and show it on search results.') }}">
                                                                        <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <label class="form-check d-flex gap-2">
                                                                        <input type="radio" name="meta_index"
                                                                            value="noindex"
                                                                            class="action-input-no-index-event form-check-input radio--input radio--md">
                                                                        <span
                                                                            class="user-select-none fs-14 title-semidark lh-24px form-check-label">{{ translate('No index') }}</span>
                                                                    </label>
                                                                    <span class="tooltip-icon" data-bs-toggle="tooltip"
                                                                        data-bs-title="{{ translate('Disallow search engines to put this web page on their list or index and do not show it on search results.') }}">
                                                                        <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="bg-white rounded p-2 p-sm-3">
                                                        <div class="row g-3">
                                                            <div class="col-sm-6">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <label class="form-check d-flex gap-2">
                                                                        <input type="checkbox" name="meta_no_follow"
                                                                            value="1"
                                                                            class="input-no-index-sub-element form-check-input_theme form-check-input checkbox--input">
                                                                        <span
                                                                            class="user-select-none fs-14 title-semidark lh-24px form-check-label">{{ translate('No Follow') }}</span>
                                                                    </label>
                                                                    <span class="tooltip-icon" data-bs-toggle="tooltip"
                                                                        data-bs-title="{{ translate('Instruct search engines not to follow links from this web page.') }}">
                                                                        <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <label class="form-check d-flex gap-2">
                                                                        <input type="checkbox" name="meta_no_archive"
                                                                            value="1"
                                                                            class="input-no-index-sub-element form-check-input_theme form-check-input checkbox--input">
                                                                        <span
                                                                            class="user-select-none fs-14 title-semidark lh-24px form-check-label">{{ translate('No Index') }}</span>
                                                                    </label>
                                                                    <span class="tooltip-icon" data-bs-toggle="tooltip"
                                                                        data-bs-title="{{ translate('Instruct search engines not to display this webpages cached or saved version.') }}">
                                                                        <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <label class="form-check d-flex gap-2">
                                                                        <input type="checkbox"
                                                                            name="meta_no_image_index" value="1"
                                                                            class="input-no-index-sub-element form-check-input_theme form-check-input checkbox--input">
                                                                        <span
                                                                            class="user-select-none fs-14 title-semidark lh-24px form-check-label">{{ translate('No Image Index') }}</span>
                                                                    </label>
                                                                    <span class="tooltip-icon" data-bs-toggle="tooltip"
                                                                        data-bs-title="{{ translate('Prevents images from being listed or indexed by search engines') }}">
                                                                        <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <label class="form-check d-flex gap-2">
                                                                        <input type="checkbox" name="meta_no_snippet"
                                                                            value="1"
                                                                            class="input-no-index-sub-element form-check-input_theme form-check-input checkbox--input">
                                                                        <span class="user-select-none fs-14 title-semidark lh-24px form-check-label">
                                                                            {{ translate('No Snippet') }}
                                                                        </span>
                                                                    </label>
                                                                    <span class="tooltip-icon" data-bs-toggle="tooltip"
                                                                        data-bs-title="{{ translate('Instruct search engines not to show a summary or snippet of this webpages content in search results.') }}">
                                                                        <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="p-xxl-20px p-2 light-box rounded h-100 h-100">
                                                    <div class="bg-white rounded p-2 p-sm-3 d-flex flex-column gap-2 h-100">
                                                        <div
                                                            class="d-flex gap-2 justify-content-between align-items-center snippet-box">
                                                            <div class="item">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <label class="form-check d-flex gap-2">
                                                                        <input type="checkbox" name="meta_max_snippet"
                                                                            value="1"
                                                                            class="form-check-input form-check-input_theme checkbox--input">
                                                                        <span class="user-select-none fs-14 title-semidark lh-24px form-check-label">
                                                                            {{ translate('Max Snippet') }}
                                                                        </span>
                                                                    </label>
                                                                    <span class="tooltip-icon" data-bs-toggle="tooltip"
                                                                        data-bs-title="{{ translate('Determine the maximum length of a snippet or preview text of the webpage.') }}">
                                                                        <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="item flex-grow-0">
                                                                <input type="number" placeholder="-1"
                                                                    class="form-control min-h-35px py-0"
                                                                    name="meta_max_snippet_value" value="-1">
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="d-flex gap-2 justify-content-between align-items-center snippet-box">
                                                            <div class="item">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <label class="form-check d-flex gap-2 m-0">
                                                                        <input type="checkbox"
                                                                            name="meta_max_video_preview" value="1"
                                                                            class="form-check-input form-check-input_theme checkbox--input">
                                                                        <span class="user-select-none fs-14 title-semidark lh-24px form-check-label">
                                                                            {{ translate('Max Video Preview') }}
                                                                        </span>
                                                                    </label>
                                                                    <span class="tooltip-icon" data-bs-toggle="tooltip"
                                                                        data-bs-title="{{ translate('Determine the maximum duration of a video preview that search engines will display') }}">
                                                                        <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="item flex-grow-0">
                                                                <input type="number" placeholder="-1"
                                                                    class="form-control min-h-35px py-0"
                                                                    name="meta_max_video_preview_value" value="-1">
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="d-flex gap-2 justify-content-between align-items-center snippet-box">
                                                            <div class="item">
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <label class="form-check d-flex gap-2 m-0">
                                                                        <input type="checkbox"
                                                                            name="meta_max_image_preview" value="1"
                                                                            class="form-check-input form-check-input_theme checkbox--input">
                                                                        <span
                                                                            class="user-select-none fs-14 title-semidark lh-24px form-check-label">{{ translate('Max Image Preview') }}</span>
                                                                    </label>
                                                                    <span class="tooltip-icon" data-bs-toggle="tooltip"
                                                                        data-bs-title="{{ translate('Determine the maximum size or dimensions of an image preview that search engines will display.') }}">
                                                                        <i class="fi fi-sr-info fs-13 text-light-gray"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="item flex-grow-0">
                                                                <div class="select-wrappers w-100">
                                                                    <select class="form-select w-100 min-h-35px fs-14 py-0"
                                                                        name="meta_max_image_preview_value">
                                                                        <option value="large">{{ translate('Large') }}</option>
                                                                        <option value="medium">{{ translate('Medium') }}</option>
                                                                        <option value="small">{{ translate('Small') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end trans3 mt-4">
                            <div
                                class="d-flex justify-content-sm-end justify-content-center gap-3 flex-grow-1 flex-grow-sm-0 action-btn-wrapper trans3">
                                <button type="reset" id="reset" class="btn btn--secondary px-4 px-sm-5">
                                    {{ translate('Reset') }}
                                </button>
                                <button type="button"
                                    class="btn btn-primary px-4 px-sm-5 product-add-requirements-check">
                                    {{ translate('Submit') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    @include("auction.web-views.partials._ai-assistant-sidebar")

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}">
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/tags-input.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'assets/auction/plugin/quill-editor/quill-editor.js') }}"></script>
    <script src="{{ theme_asset(path: 'assets/auction/plugin/quill-editor/quill-editor-init.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/customer/js/ai/auction/basic-setup-autofill.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/customer/js/ai/auction/general-setup-autofill.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/customer/js/ai/auction/shipping-policy-autofill.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/customer/js/ai/auction/auction-info-autofill.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/customer/js/ai/auction/seo-section-autofill.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/customer/js/ai/auction/ai-sidebar.js') }}"></script>
    <script>

        $(document).on('focus', '.js-auction-datetime', function () {
            if (!$(this).data('daterangepicker')) {
                $(this).daterangepicker({
                    parentEl: 'body',
                    singleDatePicker: true,
                    timePicker: true,
                    opens: 'left',
                    drops: 'down',
                    locale: {
                        format: 'DD MMM YYYY, hh:mm A'
                    }
                });
            }
        });


        $(function () {
            const $form = $('#auction_product_form');
            const $submitButton = $('.product-add-requirements-check');
            let isSubmitting = false;

            function showToast(type, message) {
                if (typeof toastMagic !== 'undefined' && typeof toastMagic[type] === 'function') {
                    toastMagic[type](message);
                    return;
                }
                if (typeof toastr !== 'undefined' && typeof toastr[type] === 'function') {
                    toastr[type](message);
                    return;
                }
                if (type === 'error') { console.error(message); return; }
                console.log(message);
            }

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $form.find('input[name="_token"]').val() }
            });

            function showErrors(messages) {
                messages.forEach((message, index) => {
                    setTimeout(() => { showToast('error', message); }, index * 250);
                });
            }

            function collectValidationErrors(response) {
                if (response?.responseJSON?.errors) {
                    return Object.values(response.responseJSON.errors).flat();
                }
                if (response?.responseJSON?.message) {
                    return [response.responseJSON.message];
                }
                return ['{{ translate('something_went_wrong') }}'];
            }

            function submitAuctionForm() {
                if (isSubmitting) return;
                const formData = new FormData($form[0]);
                isSubmitting = true;
                $submitButton.prop('disabled', true);
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () { $('#loading').addClass('d-grid'); },
                    success: function (response) {
                        if (response?.message) { showToast('success', response.message); }
                        if (response?.redirect_url) { window.location.href = response.redirect_url; }
                    },
                    error: function (response) { showErrors(collectValidationErrors(response)); },
                    complete: function () {
                        isSubmitting = false;
                        $submitButton.prop('disabled', false);
                        $('#loading').removeClass('d-grid');
                    }
                });
            }

            $submitButton.on('click', function () { submitAuctionForm(); });
            $form.on('submit', function (event) { event.preventDefault(); submitAuctionForm(); });

            // SEO auto-fill (create only) ──────────────────────────────────────
            // Auto-populate Meta Title from default-language product name on blur.
            $(document).on('blur', '.product-title-default-language', function () {
                const $metaTitle = $('#meta_title');
                if ($metaTitle.val().trim() === '') {
                    $metaTitle.val($(this).val().trim()).trigger('input');
                }
            });

            // Mirror thumbnail into Meta Image — file + visual preview.
            // DataTransfer injects the same File object so the backend receives it.
            $(document).on('change', 'input[name="thumbnail"]', function () {
                const file = this.files && this.files[0];
                if (!file) return;
                const metaInputEl = document.querySelector('input[name="meta_image"]');
                if (!metaInputEl) return;
                if ($(metaInputEl).data('user-chosen')) return;

                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    metaInputEl.files = dt.files;
                } catch (e) { /* DataTransfer unsupported — preview-only fallback */ }

                const metaCard  = metaInputEl.closest('.upload-file');
                const textbox   = metaCard.querySelector('.upload-file-textbox');
                const imgEl     = metaCard.querySelector('.upload-file-img');
                const overlay   = metaCard.querySelector('.overlay');
                const removeBtn = metaCard.querySelector('.remove_btn');

                const reader = new FileReader();
                reader.onload = function (e) {
                    if (textbox)   textbox.style.display = 'none';
                    if (imgEl)     { imgEl.src = e.target.result; imgEl.style.display = 'block'; }
                    if (overlay)   overlay.classList.add('show');
                    if (removeBtn) removeBtn.style.opacity = 1;
                    metaCard.classList.add('input-disabled');
                };
                reader.readAsDataURL(file);
            });

            // Mark meta image as user-chosen so thumbnail mirror never overwrites it.
            $(document).on('change', 'input[name="meta_image"]', function () {
                $(this).data('user-chosen', true);
            });
            // ──────────────────────────────────────────────────────────────────
        });
    </script>

@endpush
