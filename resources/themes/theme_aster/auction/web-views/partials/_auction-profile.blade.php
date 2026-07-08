
<div>
    <div class="d-lg-none d-block">
        <div class="w-100 bg-white shadow-sm rounded p-3 mb-3 justify-content-between d-flex gap-2 align-items-center">
            <h5 class="fs-18 fw-semibold title-clr text-capitalize m-0">{{ translate('Profile Info') }}</h5>
            <div>
                <button type="button" class="btn btn-primary border-0 px-12px" data-bs-toggle="offcanvas" data-bs-target="#profile_aside_btn">
                    <i class="fi fi-sr-apps"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="">
        <div class="row g-3">
            @include("auction.web-views.partials._auction-profile-sidebar")

            <div class="col-lg-9">
                @include("auction.web-views.partials._auction-sale-reports")
                @include("auction.web-views.partials._auction-lists")
                @include("auction.web-views.partials._auction-pending-product")
                <h5 class="fs-18 fw-semibold title-clr text-capitalize mb-12px">{{ translate('My Bids') }}</h5>
                <div class="">
                    <ul class="nav nav-rounded mb-2 d-flex text-nowrap flex-nowrap pb-2 d-flex overflow-auto align-items-center justify-content-start gap-xxl-20px gap-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fs-14 active" id="participated-tab" data-bs-toggle="pill" data-bs-target="#participated_action" type="button" role="tab" aria-controls="participated_action" aria-selected="true">
                                {{ translate('Participated') }} (09)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="myBid-tab1" data-bs-toggle="pill" data-bs-target="#myBid_auction1" type="button" role="tab" aria-controls="myBid_auction1" aria-selected="false">
                                {{ translate('Live') }} (09)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="myBid-tab2" data-bs-toggle="pill" data-bs-target="#myBid_auction2" type="button" role="tab" aria-controls="myBid_auction2" aria-selected="false">
                                {{ translate('Won') }} (09)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="myBid-tab3" data-bs-toggle="pill" data-bs-target="#myBid_auction3" type="button" role="tab" aria-controls="myBid_auction3" aria-selected="false">
                                {{ translate('Claimed') }} (09)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="myBid-tab4" data-bs-toggle="pill" data-bs-target="#myBid_auction4" type="button" role="tab" aria-controls="myBid_auction4" aria-selected="false">
                                {{ translate('Lost') }} (09)
                            </button>
                        </li>
                    </ul>  
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="participated_action" role="tabpanel" aria-labelledby="participated-tab" tabindex="0">
                            <div class="mb-3">
                                <h5 class="fw-semibold fs-16 title-clr mb-1">{{ translate('Participated Auctions') }}</h5>
                                <p class="m-0 fs-14 title-semidark">{{ translate('Here you can view the auctions you have participated for bidding') }}</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="item">
                                        @include("auction.web-views.partials._common-bids-card") 
                                    </div>
                                </div>                          
                            </div>
                        </div>
                        <div class="tab-pane fade" id="myBid_auction1" role="tabpanel" aria-labelledby="myBid-tab1" tabindex="0">
                            <div class="mb-3">
                                <h5 class="fw-semibold fs-16 title-clr mb-1">{{ translate('Live Bids') }}</h5>
                                <p class="m-0 fs-14 title-semidark">{{ translate('Here you can view auction you’re currently bidding on and still active.') }}</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="item">
                                       @include("auction.web-views.partials._common-bids-card") 
                                    </div>
                                </div>                            
                            </div>
                        </div>
                        <div class="tab-pane fade" id="myBid_auction2" role="tabpanel" aria-labelledby="myBid-tab2" tabindex="0">
                            <div class="mb-3">
                                <h5 class="fw-semibold fs-16 title-clr mb-1">{{ translate('Won Auction') }}</h5>
                                <p class="m-0 fs-14 title-semidark">{{ translate('Here view auction you’ve won and those are waiting for payment or processing.') }}</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="item">
                                       @include("auction.web-views.partials._common-bids-card") 
                                    </div>
                                </div>                            
                            </div>
                        </div>
                        <div class="tab-pane fade" id="myBid_auction3" role="tabpanel" aria-labelledby="myBid-tab3" tabindex="0">
                            <div class="mb-3">
                                <h5 class="fw-semibold fs-16 title-clr mb-1">{{ translate('Claimed Items') }}</h5>
                                <p class="m-0 fs-14 title-semidark">{{ translate('Here view auction you’ve won and successfully completed payment for.') }}</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="item">
                                       @include("auction.web-views.partials._common-bids-card") 
                                    </div>
                                </div>                            
                            </div>
                        </div>
                        <div class="tab-pane fade" id="myBid_auction4" role="tabpanel" aria-labelledby="myBid-tab4" tabindex="0">
                            <div class="mb-3">
                                <h5 class="fw-semibold fs-16 title-clr mb-1">{{ translate('Lost Auction') }}</h5>
                                <p class="m-0 fs-14 title-semidark">{{ translate('Here view auctions where another bidder placed a higher final bid.') }}</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="item">
                                       @include("auction.web-views.partials._common-bids-card") 
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


<div class="offcanvas offcanvas-start" tabindex="-1" id="profile_aside_btn" aria-labelledby="profile_aside_btn">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">{{ translate('Profile Information') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ translate('Close') }}"></button>
  </div>
  <div class="offcanvas-body">
    <h5 class="fs-16 fw-semibold title-clr py-12px px-10px border-bottom mb-20">{{ translate('Filter_By') }}</h5>
    @include("auction.web-views.partials._auction-profile-sidebar")
  </div>
</div>