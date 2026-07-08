@extends("auction.layouts.auction-app")

@section('title', 'auction checkout')

@section('content')
<div class="container py-4">

    <div>
        <h3 class="fs-18 mb-3 fw-semibold title-clr text-center">Cart List</h3>
        <div class="">
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card bs-border card-body">
                        <div class="">
                            <div class="rounded light-box p-xxl-20px p-3 mb-10px">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input form-check-input_theme" type="checkbox" id="enterProduct" checked>
                                    <label class="form-check-label mt-1 fs-14 mb-0 fw-bold title-clr lh-lg" for="enterProduct">Eratmart Bluetooth Headphone - Headphone</label>
                                </div>
                            </div>
                            <div class="rounded overflow-hidden mb-2">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle items-table mb-0 pb-0 table-borderless">
                                            <thead class="table-header-bg">
                                                <tr>
                                                    <th><div class="fs-14 fw-semibold">Product Details</div></th>
                                                    <th><div class="fs-14 fw-semibold">Starting Price</div></th>
                                                    <th><div class="fs-14 fw-semibold text-end">Final Bid Price</div></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-sm-center gap-10px">
                                                            <a href="#0" class="bids-details-thumbs bs-border w-70px h-70px min-w-70px d-center position-relative z-1 rounded overflow-hidden">
                                                                <img src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/thumb.png') }}" alt="" class="w-100 h-100">
                                                            </a>
                                                            <div class="">
                                                                <h2 class="mb-2 fs-13 line--limit-1 title-clr fw-semibold">
                                                                    {{ translate('Pomegranate 100% Orange Lorem ipsum is a dummy ') }}
                                                                </h2>
                                                                <div class="d-flex flex-column gap-2 auction-details-right-content">
                                                                    <div class="d-flex align-items-center gap-5px">
                                                                        <div class="minmax-xs-100px fs-14 title-semidark lh-sm">
                                                                            {{ translate('Category') }}
                                                                        </div>
                                                                        <span class="title-semidark lh-1">:</span>
                                                                        <div class="info-option2">
                                                                            <h4 class="fs-12 m-0 title-clr fw-normal">Gadget</h4>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex align-items-center gap-5px">
                                                                        <div class="minmax-xs-100px fs-14 title-semidark lh-sm">
                                                                            {{ translate('Item Condition') }}
                                                                        </div>
                                                                        <span class="title-semidark lh-1">:</span>
                                                                        <div class="info-option2">
                                                                            <h4 class="fs-12 m-0 title-clr fw-normal">New Condition</h4>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="fs-14 min-w-100px title-clr">
                                                            140.00$
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="fs-14 min-w-100px fw-bold title-clr text-end">
                                                            1125.00$
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-20">
                                <h3 class="fs-16 mb-10px fw-semibold title-clr">Order note (Optional)</h3>
                                <div class="form-group m-0">
                                    <textarea class="form-control" name="claim" id="" placeholder="Type your note here" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="card bs-border mb-20">
                                <div class="card-body">
                                    <div class="rounded light-box p-xxl-20px p-3 mb-3">
                                        <h5 class="mb-0 fs-14 title-clr fw-bold">Shipping address</h5>
                                    </div>
                                    <form>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label mb-2">Contact person name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control min-h-40" placeholder="Type your user name">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label mb-2 d-block">Phone <span class="text-danger">*</span></label>
                                                <input id="phone" type="tel" class="form-control w-100 min-h-40" placeholder="Enter your number">
                                            </div>
                                             <div class="col-md-6">
                                                <label class="form-label mb-2">Email <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control min-h-40" placeholder="Type your email">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label mb-2">Address Type</label>
                                                <select class="form-select text-light-gray min-h-40 fs-13">
                                                    <option>Select from dropdown</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label mb-2">Country <span class="text-danger">*</span></label>
                                                <select class="form-select text-light-gray min-h-40 fs-13">
                                                    <option>Select from dropdown</option>
                                                    <option>Uk</option>
                                                    <option>Us</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label mb-2">City <span class="text-danger">*</span></label>
                                                <select class="form-select text-light-gray min-h-40 fs-13">
                                                    <option>Select from dropdown</option>
                                                    <option>Dhaka</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label mb-2">Zip Code <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" placeholder="Ex: 1216">
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <label class="form-label mb-0">Address <span class="text-danger">*</span></label>
                                                    <a href="#0" type="button" class="fw-semibold fs-13 d-flex align-items-center gap-1 p-0 text-primary">
                                                        Set From Saved  <i class="fi fi-sr-land-layer-location"></i>
                                                    </a>
                                                </div>
                                                <input class="form-control" placeholder="Ex : House#38, Road#04, Demo City">
                                            </div>
                                            <div class="col-12">
                                                <div class="map-container mt-2 overflow-hidden rounded">
                                                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.617543503295!2d-73.854291!3d40.725832!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQzJzMzLjAiTiA3M8KwNTEnMTUuNCJX!5e0!3m2!1sen!2sus!4v1650000000000!5m2!1sen!2sus"
                                                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                            <div class="">
                                <div class="rounded light-box p-3 billing-address-section">
                                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                        <h5 class="mb-0 fs-18 fw-bold">Billing address</h5>
                                        <div class="form-check d-flex align-items-center">
                                            <label class="form-check-label fs-12 mb-0 title-clr mt-1 pe-33px" for="sameAddress">Same As Shipping Adress</label>
                                            <input class="form-check-input form-check-input_theme" type="checkbox" id="sameAddress" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="checkout-wrap-right">
                        <div class="d-flex flex-column gap-4">
                            <div class="p-xxl-20px p-3 card border-0 shadow-sm rounded">
                                <div class="mb-3 d-flex align-items-center justify-content-between gap-1">
                                    <h4 class="fs-18 fw-semibold title-clr mb-0 line--limit-1">
                                        Payment Method
                                    </h4>
                                    <button type="button" class="btn p-0 text-primary fs-16 outline-0">
                                        Change
                                    </button>
                                </div>
                                <div class="d-flex flex-column gap-15px">
                                    <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="fs-14 title-semidark">
                                                {{ translate('Paid by Wallet ') }}
                                            </div>
                                            <span class="title-semidark">:</span>
                                        </div>
                                        <div class="info-option2">
                                            <h4 class="fs-14 m-0 title-clr fw-semibold">$ 250.00</h4>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="fs-14 title-semidark">
                                                {{ translate('Cash on Delivery ( Due) ') }}
                                            </div>
                                            <span class="title-semidark">:</span>
                                        </div>
                                        <div class="info-option2">
                                            <h4 class="fs-14 m-0 title-clr fw-semibold">$5.00</h4>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="border-0 outline-0 light-box rounded cursor-pointer text-center fs-14 title-semidark p-3">
                                    <i class="fi fi-sr-add text-primary d-block fs-20"></i>
                                    Add Payment Method
                                </button>
                            </div>
                            <div class="p-xxl-20px p-3 card border-0 shadow-sm rounded">
                                <div class="d-flex flex-column gap-15px">
                                    <div class="mb-1">
                                        <h3 class="fs-18 mb-0">{{ translate('Order Summary') }}</h3>
                                    </div>
                                    <div class="form-grp mb-1 d-flex align-items-center gap-1 border rounded p-1 overflow-hidden">
                                        <input type="text" class="form-control min-h-30px border-0 outline-0" placeholder="Write coupon code here">
                                        <button type="button" class="btn py-2 min-h-30px px-3 btn-primary">Apply</button>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="fs-14 title-semidark">
                                                {{ translate('Sub total ') }}
                                            </div>
                                            <span class="title-semidark">:</span>
                                        </div>
                                        <div class="info-option2">
                                            <h4 class="fs-14 m-0 title-clr fw-semibold">$100.00</h4>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="fs-14 title-semidark">
                                                {{ translate('Shipping Fee') }}
                                            </div>
                                            <span class="title-semidark">:</span>
                                        </div>
                                        <div class="info-option2">
                                            <h4 class="fs-14 m-0 title-clr fw-semibold">$ 10.00</h4>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="fs-14 title-semidark">
                                                {{ translate('Tax') }}
                                            </div>
                                            <span class="title-semidark">:</span>
                                        </div>
                                        <div class="info-option2">
                                            <h4 class="fs-14 m-0 title-clr fw-semibold">$ 50.00</h4>
                                        </div>
                                    </div>
                                    <div class="border-bottom"></div>
                                    <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="fs-16 fw-semibold title-clr">
                                                {{ translate('Total') }}
                                            </div>
                                            <span class="title-semidark">:</span>
                                        </div>
                                        <div class="info-option2">
                                            <h4 class="fs-16 m-0 text-primary fw-semibold">$ 510.00</h4>
                                        </div>
                                    </div>
                                    <div class="mt-2 fs-13 py-2 px-2 badge text-wrap text-start bg-warning title-semidark fw-normal bg-opacity-10 d-flex align-items-center gap-1">
                                        <i class="fi fi-sr-info text-warning"></i>
                                        Return Policy : Return accepted for 7 days
                                    </div>
                                    <button type="button" class="btn btn-primary w-100 mt-1">
                                        Proceed to Checkout
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
<div class="offcanvas offcanvas-start" tabindex="-1" id="profile_aside_btn" aria-labelledby="profile_aside_btn">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">{{ translate('Profile Information') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <h5 class="fs-16 fw-semibold title-clr py-12px px-10px border-bottom mb-20">{{ translate('Filter_By') }}</h5>
    @include("auction.web-views.partials._auction-profile-sidebar")
  </div>
</div>
@endsection


