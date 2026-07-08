function toggleAuctionVideoProvider() {
    const $youtubeBlock = $('.youtube-link-here');
    const $fileBlock = $('.upload-video-file-here');
    if (!$youtubeBlock.length && !$fileBlock.length) return;

    const selected = $('input[name="video_provider"]:checked').val();
    if (selected === 'custom_video') {
        $youtubeBlock.addClass('d--none');
        $fileBlock.removeClass('d--none');
    } else {
        $fileBlock.addClass('d--none');
        $youtubeBlock.removeClass('d--none');
    }
}

$(function () {
    toggleAuctionVideoProvider();
});

$(document).on('change', 'input[name="video_provider"]', function () {
    toggleAuctionVideoProvider();
});

$(document).on("click", ".auction-saved-update", function () {
    let btn = $(this);
    let auctionProductId = btn.data("auction-product-id");
    let basedClass = btn.data("based-class");

    // Product-details page buttons carry data-url and are handled exclusively
    // by auction-product-details.js; skip them here to avoid double-firing.
    if (!basedClass) return;

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
        },
    });

    $.ajax({
        url: $('#route-auction-saved-products-toggle').data("route"),
        method: "POST",
        data: { auction_product_id: auctionProductId },
        success: function (data) {
            $("." + basedClass).toggleClass("is-saved", data?.is_saved);
            if (data?.message) {
                toastr.success(data.message);
            }
        },
        error: function (xhr) {
            if (xhr.status === 401) {
                $('#loginModal').modal('show');
            }
        },
    });
});
