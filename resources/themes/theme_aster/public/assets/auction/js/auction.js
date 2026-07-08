"use strict";


// See More / See Less for .details-content-wrap is handled globally by main.js
// (see main.js: $(".see-more-details").on("click", ...) and tabFunction()).


//Day Countdown
(function () {
    const flashContainers = document.querySelectorAll('.flash-deal-countdown');
    if (!flashContainers.length) return;
    flashContainers.forEach(function (flashContainer) {

        const targetDate = new Date(flashContainer.getAttribute('data-countdown')).getTime();
        function updateTimer() {

            const now = new Date().getTime();
            const diff = targetDate - now;

            if (diff < 0) {
                // flashContainer.innerHTML = "EXPIRED";
                return;
            }
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            flashContainer.querySelector('.cz-countdown-days .cz-countdown-value').innerText = days.toString().padStart(2, '0');
            flashContainer.querySelector('.cz-countdown-hours .cz-countdown-value').innerText = hours.toString().padStart(2, '0');
            flashContainer.querySelector('.cz-countdown-minutes .cz-countdown-value').innerText = minutes.toString().padStart(2, '0');
            flashContainer.querySelector('.cz-countdown-seconds .cz-countdown-value').innerText = seconds.toString().padStart(2, '0');
        }
        updateTimer();
        setInterval(updateTimer, 1000);

    });
})();

//IntelIcon Initialize
(function() {
    const telInput = document.querySelector('input[type="tel"]');
    if (telInput) {
        window.intlTelInput(telInput, {
            initialCountry: "bd",
            preferredCountries: ["bd", "us", "gb"],
            autoPlaceholder: "polite",
        });
    }
})();

$(document).on('click', 'button[type="reset"]', function () {

    const form = $(this).closest('form');

    form.find('.multiple-select2').each(function () {
        $(this).val($(this).data('old') || []).trigger('change');
    });

});

$('.multiple-select2').each(function () {
    $(this).data('old', $(this).val());
});

$("#reset").on("click", function () {
    let placeholderImg = $("#placeholderImg").data("img");
    $("#viewer").attr("src", placeholderImg);
    $(".spartan_remove_row").click();
});





//Ending Soon
// $(document).ready(function () {
//     let directionFromSession = "ltr";
//     $(".ending_soon_slidewrap").each(function(){
//         let maxItems = $(this).data('slide-items');
//         $(this).owlCarousel({
//             autoplay: true,
//             margin: 20,
//             nav: true,
//             navText:
//             directionFromSession === "rtl"
//                 ? [
//                         "<i class='fi fi-rr-angle-right fs-14'></i>",
//                         "<i class='fi fi-rr-angle-left fs-14'></i>",
//                     ]
//                 : [
//                         "<i class='fi fi-rr-angle-left fs-14'></i>",
//                         "<i class='fi fi-rr-angle-right fs-14'></i>",
//                     ],
//                     dots: false,
//                     autoplayHoverPause: true,
//                     rtl: directionFromSession === "rtl",
//                     ltr: directionFromSession === "ltr",

//             responsive: {
//                 0: {
//                     items: 1,
//                     loop: maxItems > 1,
//                     margin: 12,
//                 },
//                 360: {
//                     items: 1.02,
//                     loop: maxItems > 1.02,
//                     margin: 12,
//                 },
//                 425: {
//                     items: 2,
//                     loop: maxItems > 2,
//                     margin: 12,
//                 },
//                 540: {
//                     items: 2,
//                     loop: maxItems > 2,
//                     margin: 12,
//                 },
//                 576: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 768: {
//                     items: 3,
//                     loop: maxItems > 3
//                 },
//                 992: {
//                     items: 4,
//                     loop: maxItems > 4
//                 },
//                 1200: {
//                     items: 5,
//                     loop: maxItems > 5
//                 },
//             },
//         });
//     })
// });


// var swiper = new Swiper(".ending_soon_slidewrap", {
//     slidesPerView: 1,
//     spaceBetween: 20,
//     autoplay: false,
//     loop: true,
//     navigation: {
//         nextEl: '.swiper-button-next--ending-soon',
//         prevEl: '.swiper-button-prev--ending-soon',
//     },
//     breakpoints: {
//         0: {
//             slidesPerView: 1,
//             spaceBetween: 12
//         },
//         360: {
//             slidesPerView: 1.02,
//             spaceBetween: 12
//         },
//         425: {
//             slidesPerView: 2,
//             spaceBetween: 12
//         },
//         768: {
//             slidesPerView: 3,
//             spaceBetween: 20
//         },
//         992: {
//             slidesPerView: 4,
//             spaceBetween: 20
//         },
//         1200: {
//             slidesPerView: 4.3,
//             spaceBetween: 20
//         }
//     },
// });




// const endingSoonSlidewrap = new Swiper(".ending_soon_slidewrap", {
//   slidesPerView: 1,
//   spaceBetween: 10,
//   slidesPerGroup: 1,
//   loop: false,

//   navigation: {
//     nextEl: ".swiper-button-next--ending-soon",
//     prevEl: ".swiper-button-prev--ending-soon",
//   },

//   pagination: {
//     el: ".swiper-pagination",
//     clickable: true,
//   },

//   breakpoints: {
//     0: {
//       slidesPerView: 1,
//       spaceBetween: 15,
//     },
//     360: {
//       slidesPerView: 1.02,
//       spaceBetween: 15,
//     },
//     576: {
//       slidesPerView: 2,
//       spaceBetween: 16,
//     },
//     768: {
//       slidesPerView: 3,
//       spaceBetween: 16,
//     },
//     992: {
//       slidesPerView: 2,
//       spaceBetween: 20,
//     },
//     1200: {
//       slidesPerView: 5,
//       spaceBetween: 20,
//       slidesPerGroup: 1,
//     },
//   },
// });
//Categories
// $(document).ready(function () {
//     let directionFromSession = "ltr";
//     $(".categories-product-slidewrap").each(function(){
//         let maxItems = $(this).data('slide-items');
//         $(this).owlCarousel({
//             autoplay: false,
//             margin: 20,
//             nav: true,
//             navText:
//             directionFromSession === "rtl"
//                 ? [
//                         "<i class='fi fi-rr-angle-right fs-14'></i>",
//                         "<i class='fi fi-rr-angle-left fs-14'></i>",
//                     ]
//                 : [
//                         "<i class='fi fi-rr-angle-left fs-14'></i>",
//                         "<i class='fi fi-rr-angle-right fs-14'></i>",
//                     ],
//                     dots: false,
//                     autoplayHoverPause: true,
//                     rtl: directionFromSession === "rtl",
//                     ltr: directionFromSession === "ltr",

//             responsive: {
//                 0: {
//                     items: 1,
//                     loop: maxItems > 1
//                 },
//                 360: {
//                     items: 1.02,
//                     loop: maxItems > 1.02
//                 },
//                 480: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 540: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 576: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 768: {
//                     items: 3,
//                     loop: maxItems > 3
//                 },
//                 992: {
//                     items: 4,
//                     loop: maxItems > 4
//                 },
//                 1200: {
//                     items: 4.6,
//                     loop: maxItems > 4.6
//                 },
//             },
//         });
//     })
// });

//Recently Viewed
// $(document).ready(function () {
//     let directionFromSession = "ltr";
//     $(".recently_views_slidewrap").each(function(){
//         let maxItems = $(this).data('slide-items');
//         $(this).owlCarousel({
//             autoplay: false,
//             margin: 20,
//             nav: true,
//             navText:
//             directionFromSession === "rtl"
//                 ? [
//                         "<i class='fi fi-rr-angle-right fs-14'></i>",
//                         "<i class='fi fi-rr-angle-left fs-14'></i>",
//                     ]
//                 : [
//                         "<i class='fi fi-rr-angle-left fs-14'></i>",
//                         "<i class='fi fi-rr-angle-right fs-14'></i>",
//                     ],
//                     dots: false,
//                     autoplayHoverPause: true,
//                     rtl: directionFromSession === "rtl",
//                     ltr: directionFromSession === "ltr",

//             responsive: {
//                 0: {
//                     items: 1,
//                     loop: maxItems > 1
//                 },
//                 360: {
//                     items: 1.02,
//                     loop: maxItems > 1.02
//                 },
//                 480: {
//                     items: 1,
//                     loop: maxItems > 1
//                 },
//                 560: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 576: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 768: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 992: {
//                     items: 3,
//                     loop: maxItems > 3
//                 },
//                 1200: {
//                     items: 3.6,
//                     loop: maxItems > 3.6
//                 },
//             },
//         });
//     })
// });

//Upcoming Auction
// $(document).ready(function () {
//     let directionFromSession = "ltr";
//     $(".upcoming_auction_slidewrap").each(function(){
//         let maxItems = $(this).data('slide-items');
//         $(this).owlCarousel({
//             autoplay: false,
//             margin: 20,
//             nav: true,
//             navText:
//             directionFromSession === "rtl"
//                 ? [
//                         "<i class='fi fi-rr-angle-right fs-14'></i>",
//                         "<i class='fi fi-rr-angle-left fs-14'></i>",
//                     ]
//                 : [
//                         "<i class='fi fi-rr-angle-left fs-14'></i>",
//                         "<i class='fi fi-rr-angle-right fs-14'></i>",
//                     ],
//                     dots: false,
//                     autoplayHoverPause: true,
//                     rtl: directionFromSession === "rtl",
//                     ltr: directionFromSession === "ltr",
//             responsive: {
//                 0: {
//                     items: 1,
//                     loop: maxItems > 1,
//                     margin: 12,
//                 },
//                 425: {
//                     items: 2,
//                     loop: maxItems > 2,
//                     margin: 12,
//                 },
//                 480: {
//                     items: 2,
//                     loop: maxItems > 2,
//                     margin: 12,
//                 },
//                 540: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 576: {
//                     items: 1.3,
//                     loop: maxItems > 1.3
//                 },
//                 768: {
//                     items: 2.3,
//                     loop: maxItems > 2.3
//                 },
//                 992: {
//                     items: 3.2,
//                     loop: maxItems > 3.2
//                 },
//                 1200: {
//                     items: 4.6,
//                     loop: maxItems > 4.6
//                 },
//             },
//         });
//     })
// });

//Phone & Telecom
// $(document).ready(function () {
//     let directionFromSession = "ltr";
//     $(".including_product_slidewrap").each(function(){
//         let maxItems = $(this).data('slide-items');
//         $(this).owlCarousel({
//             autoplay: false,
//             margin: 20,
//             nav: true,
//             navText:
//             directionFromSession === "rtl"
//                 ? [
//                         "<i class='fi fi-rr-angle-right fs-14'></i>",
//                         "<i class='fi fi-rr-angle-left fs-14'></i>",
//                     ]
//                 : [
//                         "<i class='fi fi-rr-angle-left fs-14'></i>",
//                         "<i class='fi fi-rr-angle-right fs-14'></i>",
//                     ],
//                     dots: false,
//                     autoplayHoverPause: true,
//                     rtl: directionFromSession === "rtl",
//                     ltr: directionFromSession === "ltr",

//             responsive: {
//                 0: {
//                     items: 1,
//                     loop: maxItems > 1
//                 },
//                 360: {
//                     items: 1.02,
//                     loop: maxItems > 1.02
//                 },
//                 480: {
//                     items: 1,
//                     loop: maxItems > 1
//                 },
//                 560: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 576: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 768: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 992: {
//                     items: 2.6,
//                     loop: maxItems > 2.6
//                 },
//                 1200: {
//                     items: 2.8,
//                     loop: maxItems > 2.8
//                 },
//             },
//         });
//     })
// });

//Phone & Telecom
// $(document).ready(function () {
//     let directionFromSession = "ltr";
//     $(".footer-top-slider").each(function(){
//         let maxItems = $(this).data('slide-items');
//         $(this).owlCarousel({
//             autoplay: false,
//             margin: 20,
//             nav: false,
//             responsive: {
//                 0: {
//                     margin: 12,
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 360: {
//                     margin: 12,
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 480: {
//                     margin: 12,
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 560: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 576: {
//                     items: 2,
//                     loop: maxItems > 2
//                 },
//                 768: {
//                     items: 3,
//                     loop: maxItems > 3
//                 },
//                 992: {
//                     items: 4,
//                     loop: maxItems > 4
//                 },
//                 1200: {
//                     items: 4,
//                     loop: maxItems > 4
//                 },
//             },
//         });
//     })
// });

//Product Details slider
// $(document).ready(function () {
//     const isRTL = $("html").attr("dir") === "rtl";
//     $(".product-slider").each(function () {
//         const $wrapper = $(this);
//         const $big = $wrapper.find(".big-slider");
//         const $thumb = $wrapper.find(".thumb-slider");
//         if (!$big.length || !$thumb.length) return;

//         // ==BIG SLIDER==
//         $big.owlCarousel({
//             items: 1,
//             margin: 10,
//             loop: false,
//             dots: false,
//             nav: false,
//             rtl: isRTL,
//             smartSpeed: 500
//         });
//         // ==THUMB SLIDER==
//         $thumb.owlCarousel({
//             items: 3,
//             margin: 18,
//             loop: false,
//             dots: false,
//             nav: false,
//             rtl: isRTL,
//             smartSpeed: 400
//         });
//         setTimeout(function () {
//             const currentIndex = $big.find(".owl-item.active").index();
//             setActiveThumb(currentIndex);
//         }, 50);

//         // ==BIG CHANGE EVENT==
//         $big.on("changed.owl.carousel", function (event) {
//             const index = event.item.index;
//             setActiveThumb(index);
//         });
//         // ==THUMB CLICK==
//         $thumb.on("click", ".owl-item", function () {
//             const index = $(this).index();
//             $big.trigger("to.owl.carousel", [index, 400, true]);
//             setActiveThumb(index);
//         });
//         // ==ACTIVE CONTROLLER==
//         function setActiveThumb(index) {
//             $thumb.find(".owl-item")
//                 .removeClass("current")
//                 .eq(index)
//                 .addClass("current");
//             const visibleStart = $thumb.find(".owl-item.active").first().index();
//             const visibleEnd = $thumb.find(".owl-item.active").last().index();
//             if (index > visibleEnd) {
//                 $thumb.trigger("to.owl.carousel", [index, 300, true]);
//             }

//             if (index < visibleStart) {
//                 $thumb.trigger("to.owl.carousel", [index, 300, true]);
//             }
//         }
//     });
//     // $(".product-slider").each(function () {
//     //     const $wrapper = $(this);
//     //     const $big = $wrapper.find(".big-slider");
//     //     // INIT EASYZOOM FIRST SLIDE
//     //     if ($.fn.easyZoom) {
//     //         $big.find(".owl-item.active .easyzoom").easyZoom();
//     //     }
//     //     // RE-INIT ON SLIDE CHANGE
//     //     $big.on("changed.owl.carousel", function () {
//     //         // Destroy all zoom instances
//     //         $wrapper.find(".easyzoom").each(function () {
//     //             const api = $(this).data("easyZoom");
//     //             if (api) api.teardown();
//     //         });
//     //         // Init only active slide
//     //         const $current = $big.find(".owl-item.active .easyzoom");

//     //         if ($current.length) {
//     //             $current.easyZoom();
//     //         }
//     //     });
//     // });
// });

//img to conver svg
$("img.svg").each(function () {
    let $img = jQuery(this);
    let imgID = $img.attr("id");
    let imgClass = $img.attr("class");
    let imgURL = $img.attr("src");

    jQuery.get(
        imgURL,
        function (data) {
            let $svg = jQuery(data).find("svg");
            if (typeof imgID !== "undefined") {
                $svg = $svg.attr("id", imgID);
            }
            if (typeof imgClass !== "undefined") {
                $svg = $svg.attr("class", imgClass + " replaced-svg");
            }

            $svg = $svg.removeAttr("xmlns:a");
            if (
                !$svg.attr("viewBox") &&
                $svg.attr("height") &&
                $svg.attr("width")
            ) {
                $svg.attr(
                    "viewBox",
                    "0 0 " + $svg.attr("height") + " " + $svg.attr("width")
                );
            }

            $img.replaceWith($svg);
        },
        "xml"
    );
});

//Countdown
$(document).ready(function () {
    function parseEndDate(endValue) {
        if (endValue == null) return null;

        if (typeof endValue === 'number' || /^[0-9]+$/.test(String(endValue).trim())) {
            let timestamp = Number(endValue);
            if (timestamp > 0 && timestamp < 1e12) {
                timestamp *= 1000;
            }
            return new Date(timestamp);
        }

        let date = new Date(endValue);
        if (!isNaN(date)) return date;

        date = new Date(String(endValue).replace(' ', 'T'));
        if (!isNaN(date)) return date;

        const parts = String(endValue).split(/[- :]/);
        if (parts.length < 3) return null;

        return new Date(
            parseInt(parts[0], 10),
            parseInt(parts[1], 10) - 1,
            parseInt(parts[2], 10),
            parseInt(parts[3] || 0, 10),
            parseInt(parts[4] || 0, 10),
            parseInt(parts[5] || 0, 10)
        );
    }

    function startCountdown() {
        $('.countdown').each(function () {
            let $this = $(this);
            let endDate = parseEndDate($this.data('end'));
            if (!endDate || isNaN(endDate.getTime())) return;

            const endTime = endDate.getTime();
            let timer = null;

            function updateTimer() {
                let now = Date.now();
                let distance = endTime - now;

                if (distance <= 0) {
                    if (timer) clearInterval(timer);
                    $this.html("<span class='text-danger fw-bold'>Ended</span>");
                    return;
                }

                let hours = Math.floor(distance / (1000 * 60 * 60));
                let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                $this.find('.hours').text(hours.toString().padStart(2, '0'));
                $this.find('.minutes').text(minutes.toString().padStart(2, '0'));
                $this.find('.seconds').text(seconds.toString().padStart(2, '0'));
            }

            updateTimer();
            timer = setInterval(updateTimer, 1000);
        });
    }
    startCountdown();
});

//Categories accordion menu
$(".menu--caret").on("click", function (e) {
    var element = $(this).closest(".menu--caret-accordion");
    if (element.hasClass("open")) {
        element.removeClass("open");
        element.find(".menu--caret-accordion").removeClass("open");
        element.find(".card-body").slideUp(300, "swing");
    } else {
        element.addClass("open");
        element.children(".card-body").slideDown(300, "swing");
        element
            .siblings(".menu--caret-accordion")
            .children(".card-body")
            .slideUp(300, "swing");
        element.siblings(".menu--caret-accordion").removeClass("open");
        element
            .siblings(".menu--caret-accordion")
            .find(".menu--caret-accordion")
            .removeClass("open");
        element
            .siblings(".menu--caret-accordion")
            .find(".card-body")
            .slideUp(300, "swing");
    }
});

//Tooltip Initialize
$(function () {
    $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' })
})

//Daterangepicker Initialize
$(document).on('focus', '.js-select2-date', function () {
    if (!$(this).data('daterangepicker')) {
        $(this).daterangepicker({
            parentEl: 'body',
            singleDatePicker: true,
            timePicker: false,
            opens: 'left',
            drops: 'down',
            locale: {
                format: 'DD MMM YYYY, hh:mm A'
            }
        });
    }
});

$(document).ready(function () {
    $(".image-uploader__zip").on("change", function (event) {
        const file = event.target.files[0];
        const wrapper = $(this).closest(".image-uploader");
        const target = wrapper.find(".image-uploader__title");
        const removeBtn = wrapper.find(".zip-remove-btn");
        const icon = wrapper.find(".upload-preview-icon");

        const allowedTypes = ["application/pdf", "audio/mpeg", "video/mp4", "video/webm"];

        if (file) {

            if (!allowedTypes.includes(file.type)) {
                toastMagic.error("Only PDF, MP3, MP4 and WEBM files are allowed");
                $(this).val(null);

                target.text("Upload File").addClass("text-info");
                icon.attr("src", wrapper.data("default-icon"));
                removeBtn.hide();
                return;
            }

            target.text(file.name).removeClass("text-info");

            let newIcon = wrapper.data("default-icon");

            if (file.type === "application/pdf") {
                newIcon = wrapper.data("pdf-icon");
            }
            if (file.type === "audio/mpeg") {
                newIcon = wrapper.data("mp3-icon");
            }
            if (file.type === "video/mp4") {
                newIcon = wrapper.data("mp4-icon");
            }
            if (file.type === "video/webm") {
                newIcon = wrapper.data("mp4-icon");
            }

            icon.attr("src", newIcon);

            removeBtn.show();
        } else {
            target.text("Upload File").addClass("text-info");
            icon.attr("src", wrapper.data("default-icon"));
            removeBtn.hide();
        }
    });

    $(".image-uploader .zip-remove-btn").on("click", function () {
        const wrapper = $(this).closest(".image-uploader");

        wrapper.find(".image-uploader__zip").val(null);
        wrapper.find(".image-uploader__title")
            .text("Upload File")
            .addClass("text-info");

        wrapper.find(".upload-preview-icon")
            .attr("src", wrapper.data("default-icon"));

        $(this).hide();
    });

    function toggleVideoType() {
        let selected = $('input[name="upload_video_type"]:checked').val();

        if (selected === 'option1') {
            $('.youtube-link-here').show();
            $('.upload-video-file-here').hide();
        } else if (selected === 'option2') {
            $('.youtube-link-here').hide();
            $('.upload-video-file-here').show();
        }
    }

    toggleVideoType();

    $(document).on('change', 'input[name="upload_video_type"]', function () {
        toggleVideoType();
    });

    $(document).on('reset', 'form', function () {
        let form = $(this);

        setTimeout(function () {
            toggleVideoType();
        }, 0);
    });

});

