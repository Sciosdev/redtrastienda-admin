<template id="auction-notif-item-template">
    <div class="auction-notification-item notification-active border border-primary bg-primary bg-opacity-10 rounded-2 p-3 d-flex align-items-start gap-3"
         style="cursor:pointer;">
        <div class="flex-shrink-0 rounded-circle overflow-hidden border" style="width:40px;height:40px;min-width:40px;">
            <img class="js-notif-icon w-100 h-100 object-fit-cover"
                 src="{{ dynamicAsset(path: 'public/assets/front-end/auction/images/congrt-bid.png') }}"
                 alt="">
        </div>
        <div class="flex-grow-1 overflow-hidden">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                <h6 class="js-notif-title mb-0 fs-13 fw-semibold text-truncate"></h6>
                <span class="js-notif-time text-primary fs-11 flex-shrink-0 auction-notif-time"></span>
            </div>
            <p class="js-notif-message mb-0 fs-12 text-muted text-truncate"></p>
        </div>
    </div>
</template>
