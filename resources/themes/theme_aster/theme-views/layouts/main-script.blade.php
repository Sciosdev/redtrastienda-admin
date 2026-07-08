@php
    use Illuminate\Support\Facades\Cookie;
@endphp

@php($recaptcha = getWebConfig(name: 'recaptcha'))
<span id="get-google-recaptcha-key"
data-value="{{ isset($recaptcha) && $recaptcha['status'] == 1 ? $recaptcha['site_key'] : '' }}"></span>

<script src="{{ theme_asset('assets/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ theme_asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="{{ theme_asset('assets/plugins/swiper/swiper-bundle.min.js') }}"></script>
<script src="{{ theme_asset('assets/plugins/sweet_alert/sweetalert2.js') }}"></script>
<script src="{{ theme_asset('assets/plugins/magnific-popup-1.1.0/jquery.magnific-popup.js') }}"></script>
<script src="{{ theme_asset('assets/plugins/easyzoom/easyzoom.min.js') }}"></script>
<script src="{{ theme_asset('assets/js/toastr.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/intl-tel-input/js/utils.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/intl-tel-input/js/intlTelInout-validation.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/apexchart/apexcharts.js') }}"></script>

<script src="{{ theme_asset(path: 'assets/auction/js/spartan-multi-image-picker.min.js') }}"></script>
<script src="{{ theme_asset(path: 'assets/auction/js/multiple-image-upload.js') }}"></script>
<script src="{{ theme_asset(path: 'assets/auction/js/single-image-upload.js') }}"></script>

<script src="{{ theme_asset(path: 'assets/auction/plugin/quill-editor/quill-editor.js') }}"></script>
<script src="{{ theme_asset(path: 'assets/auction/plugin/quill-editor/quill-editor-init.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/daterangepicker/moment.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/daterangepicker/daterangepicker.min.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/select2/select2.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/select2/select-multiple-file.js') }}"></script>


<script src="{{ theme_asset('assets/js/main.js') }}"></script>
<script src="{{ theme_asset('assets/auction/js/auction.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/backend/file-validation/polyfills.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/file-validation/just-validate.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/file-validation/form-advance-validation.js') }}"></script>


@if (isset($recaptcha) && $recaptcha['status'] == 1)
    <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptcha['site_key'] }}"></script>
@endif
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/google-recaptcha/google-recaptcha-init.js') }}"></script>

<script>
    let placeholderImageUrl = "{{ dynamicAsset(path: 'public/assets/new/back-end/img/svg/image-upload.svg') }}";
    const iconPath = "{{ dynamicAsset(path: 'public/assets/new/back-end/img/icons/file.svg') }}";
</script>

@if(env('APP_MODE') == 'demo')
    <script>
        'use strict'
        function checkDemoResetTime() {
            let currentMinute = new Date().getMinutes();
            if (currentMinute > 55 && currentMinute <= 60) {
                $('#demo-reset-warning').addClass('active');
            } else {
                $('#demo-reset-warning').removeClass('active');
            }
        }
        checkDemoResetTime();
        setInterval(checkDemoResetTime, 60000);
    </script>
@endif

<script>
    document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.traking-slide-wrap').forEach(wrapper => {
        const container = wrapper.querySelector('.nav');
        if (!container) return;

        const btnPrevWrap = wrapper.querySelector('.button-prev');
        const btnNextWrap = wrapper.querySelector('.button-next');
        const item = wrapper.querySelector('.traking-item');

        wrapper.querySelectorAll('.traking-item').forEach(el => {
            el.style.flex = '0 0 auto';
        });
        function updateArrows() {
            const hasOverflow = container.scrollWidth > container.clientWidth;
            if (!hasOverflow) {
                btnPrevWrap?.style.setProperty('display', 'none');
                btnNextWrap?.style.setProperty('display', 'none');
                return;
            }
            const atStart = container.scrollLeft <= 0;
            const atEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - 1;
            btnPrevWrap?.style.setProperty('display', atStart ? 'none' : 'flex');
            btnNextWrap?.style.setProperty('display', atEnd ? 'none' : 'flex');
        }
        wrapper.querySelector('.btn-click-prev')?.addEventListener('click', () => {
            const itemWidth = item?.offsetWidth || 0;
            container.scrollBy({ left: -itemWidth, behavior: 'smooth' });
        });
        wrapper.querySelector('.btn-click-next')?.addEventListener('click', () => {
            const itemWidth = item?.offsetWidth || 0;
            container.scrollBy({ left: itemWidth, behavior: 'smooth' });
        });
        container.addEventListener('scroll', updateArrows);
        window.addEventListener('resize', updateArrows);
        new MutationObserver(updateArrows).observe(container, { childList: true, subtree: true });
        new ResizeObserver(updateArrows).observe(container);
        // Initial check
        updateArrows();
    });
});
</script>

<script>
    'use strict';
    $('.delete-action').on('click', function () {
        Swal.fire({
            title: '{{translate("are_you_sure")}}?',
            text: $(this).data('message'),
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '{{$web_config['primary_color']}}',
            cancelButtonText: '{{translate('no')}}',
            confirmButtonText: '{{translate('yes')}}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                location.href = $(this).data('action');
            }
        })
    })
</script>

@if ($errors->any())
    <script>
        'use strict';
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif
<script>
    'use strict';
    let cookieSection = $('#cookie-section');
    @php($cookie = $web_config['cookie_setting'] ? json_decode($web_config['cookie_setting']['value'], true):null)
    let cookie_content = `
        <div class="cookies active absolute-white py-4">
            <div class="container">
                <h4 class="absolute-white mb-3">{{translate('Your_Privacy_Matter')}}</h4>
                <p>{{ $cookie ? $cookie['cookie_text'] : '' }}</p>
                <div class="d-flex gap-3 justify-content-end mt-4">
                    <button type="button" class="btn absolute-white btn-link" id="cookie-reject">{{translate('no_thanks')}}</button>
                    <button type="button" class="btn btn-primary" id="cookie-accept">{{translate('yes_i_Accept')}}</button>
                </div>
            </div>
        </div>
        `;
    $(document).on('click', '#cookie-accept', function () {
        document.cookie = '6valley_cookie_consent=accepted; max-age=' + 60 * 60 * 24 * 30;
        cookieSection.hide();
    });
    $(document).on('click', '#cookie-reject', function () {
        document.cookie = '6valley_cookie_consent=reject; max-age=' + 60 * 60 * 24;
        cookieSection.hide();
    });

    $(document).ready(function () {
        if (document.cookie.indexOf("6valley_cookie_consent=accepted") !== -1) {
            cookieSection.hide();
        } else if (document.cookie.indexOf("6valley_cookie_consent=reject") !== -1) {
            cookieSection.hide();
        } else {
            cookieSection.html(cookie_content).show();
        }
    });
</script>

@if(!auth('customer')->check())
    <script>
        "use strict";
        $(document).ready(function() {
            const currentUrl = new URL(window.location.href);
            const referral_code_parameter = new URLSearchParams(currentUrl.search).get("referral_code");

            if (referral_code_parameter) {
                $('#registerModal').modal('show');
                let referralCode =  $('#referral_code');
                if (referralCode.length) {
                    referralCode.val(referral_code_parameter);
                }
            }
        });
    </script>
@endif

<script>
    "use strict";
    let errorMessages = {
        valueMissing: $('.please_fill_out_this_field').data('text')
    };

    $('input').each(function () {
        let $el = $(this);
        $el.on('invalid', function (event) {
            let target = event.target,
                validity = target.validity;
            target.setCustomValidity("");
            if (!validity.valid) {
                if (validity.valueMissing) {
                    target.setCustomValidity($el.data('errorRequired') || errorMessages.valueMissing);
                }
            }
        });
    });

    $('textarea').each(function () {
        let $el = $(this);
        $el.on('invalid', function (event) {
            let target = event.target,
                validity = target.validity;
            target.setCustomValidity("");
            if (!validity.valid) {
                if (validity.valueMissing) {
                    target.setCustomValidity($el.data('errorRequired') || errorMessages.valueMissing);
                }
            }
        });
    });
</script>
<script>
    $(function () {
        $(".price-range-wrapper").each(function () {

            let wrapper = $(this);
            let slider = wrapper.find(".price_range_slider");
            let minThumb = wrapper.find(".thumb_min");
            let maxThumb = wrapper.find(".thumb_max");
            let range = wrapper.find(".slider-range");
            let minInput = wrapper.find(".min_price");
            let maxInput = wrapper.find(".max_price");

            let sliderMin = slider.data('min-value') ?? 0;
            let sliderMax = slider.data('max-value') ?? 100000;

            let minValue = sliderMin;
            let maxValue = sliderMax;

            let isRtl = $('html').attr('dir') === 'rtl';

            function clamp(value, min, max) {
                return Math.min(Math.max(value, min), max);
            }

            function updateSlider() {
                let sliderWidth = slider.width();

                let minLeft = ((minValue - sliderMin) / (sliderMax - sliderMin)) * sliderWidth;
                let maxLeft = ((maxValue - sliderMin) / (sliderMax - sliderMin)) * sliderWidth;

                if (isRtl) {
                    minLeft = sliderWidth - minLeft;
                    maxLeft = sliderWidth - maxLeft;
                }

                minThumb.css(isRtl ? "right" : "left", minLeft + "px");
                maxThumb.css(isRtl ? "right" : "left", maxLeft + "px");

                range.css({
                    [isRtl ? 'right' : 'left']: Math.min(minLeft, maxLeft) + "px",
                    width: Math.abs(maxLeft - minLeft) + "px",
                });

                minInput.val(minValue);
                maxInput.val(maxValue);
            }

            function handleDrag(thumb, isMinThumb) {

                thumb.on("mousedown touchstart", function (e) {

                    e.preventDefault();
                    let startX = e.pageX || e.originalEvent.touches[0].pageX;
                    let startValue = isMinThumb ? minValue : maxValue;
                    let sliderWidth = slider.width();

                    $(document).on("mousemove.slider touchmove.slider", function (e) {

                        let pageX = e.pageX || e.originalEvent.touches[0].pageX;
                        let deltaX = isRtl ? (startX - pageX) : (pageX - startX);
                        let valueChange = (deltaX / sliderWidth) * (sliderMax - sliderMin);

                        let newValue = clamp(startValue + valueChange, sliderMin, sliderMax);
                        newValue = Math.round(newValue);

                        if (isMinThumb) {
                            minValue = Math.min(newValue, maxValue);
                        } else {
                            maxValue = Math.max(newValue, minValue);
                        }

                        updateSlider();
                    });

                    $(document).on("mouseup.slider touchend.slider", function () {
                        $(document).off(".slider");
                    });
                });
            }

            minInput.on("input", function () {
                let val = parseInt($(this).val(), 10);
                if (!isNaN(val)) {
                    minValue = clamp(val, sliderMin, maxValue);
                    updateSlider();
                }
            });

            maxInput.on("input", function () {
                let val = parseInt($(this).val(), 10);
                if (!isNaN(val)) {
                    maxValue = clamp(val, minValue, sliderMax);
                    updateSlider();
                }
            });

            handleDrag(minThumb, true);
            handleDrag(maxThumb, false);

            updateSlider();
        });

        $('.duration_range_slider').each(function () {

            let slider = $(this);
            let wrapper = slider.closest('.price-range-wrapper');

            let minThumb = wrapper.find(".duration_thumb_min");
            let maxThumb = wrapper.find(".duration_thumb_max");
            let range = wrapper.find(".duration_slider_range");
            let minInput = wrapper.find(".duration_min");
            let maxInput = wrapper.find(".duration_max");
            let typeSelect = wrapper.find(".duration_time_type");

            let sliderMin = slider.data('min-value') ?? 0;

            let maxMin = slider.data('max-min');
            let maxHour = slider.data('max-hour');
            let maxDay = slider.data('max-day');

            let sliderMax = maxMin;

            let minValue = sliderMin;
            let maxValue = sliderMax;

            let isRtl = $('html').attr('dir') === 'rtl';

            function clamp(value, min, max) {
                return Math.min(Math.max(value, min), max);
            }

            function updateSlider() {
                let sliderWidth = slider.width();
                if (sliderMax === sliderMin) return;

                let percentMin = (minValue - sliderMin) / (sliderMax - sliderMin);
                let percentMax = (maxValue - sliderMin) / (sliderMax - sliderMin);

                let minLeft = percentMin * sliderWidth;
                let maxLeft = percentMax * sliderWidth;

                if (isRtl) {
                    minLeft = sliderWidth - minLeft;
                    maxLeft = sliderWidth - maxLeft;
                }

                minThumb.css(isRtl ? "insetInlineEnd" : "insetInlineStart", minLeft + "px");
                maxThumb.css(isRtl ? "insetInlineEnd" : "insetInlineStart", maxLeft + "px");

                range.css({
                    [isRtl ? 'insetInlineEnd' : 'insetInlineStart']: Math.min(minLeft, maxLeft) + "px",
                    width: Math.abs(maxLeft - minLeft) + "px",
                });

                minInput.val(minValue);
                maxInput.val(maxValue);
            }

            function updateMaxByType() {
                let type = typeSelect.val();

                if (type === 'min') sliderMax = maxMin;
                else if (type === 'hour') sliderMax = maxHour;
                else if (type === 'day') sliderMax = maxDay;

                minValue = sliderMin;
                maxValue = sliderMax;

                slider.attr('data-max-value', sliderMax);

                updateSlider();
            }

            function handleDrag(thumb, isMinThumb) {

                thumb.on("mousedown touchstart", function (e) {
                    e.preventDefault();

                    let startX = e.pageX || e.originalEvent.touches[0].pageX;
                    let startValue = isMinThumb ? minValue : maxValue;

                    $(document).on("mousemove.slider touchmove.slider", function (e) {

                        let pageX = e.pageX || e.originalEvent.touches[0].pageX;
                        if (!pageX) return;

                        let sliderWidth = slider.width();
                        let currentMax = sliderMax;

                        let deltaX = isRtl ? (startX - pageX) : (pageX - startX);
                        let valueChange = (deltaX / sliderWidth) * (currentMax - sliderMin);

                        let newValue = clamp(Math.round(startValue + valueChange), sliderMin, currentMax);

                        if (isMinThumb) {
                            minValue = Math.min(newValue, maxValue);
                        } else {
                            maxValue = Math.max(newValue, minValue);
                        }

                        updateSlider();
                    });

                    $(document).on("mouseup.slider touchend.slider", function () {
                        $(document).off(".slider");
                    });
                });
            }

            minInput.on("input", function () {
                let val = parseInt($(this).val());
                if (!isNaN(val)) {
                    minValue = clamp(val, sliderMin, maxValue);
                    updateSlider();
                }
            });

            maxInput.on("input", function () {
                let val = parseInt($(this).val());
                if (!isNaN(val)) {
                    maxValue = clamp(val, minValue, sliderMax);
                    updateSlider();
                }
            });

            typeSelect.on("change", function () {
                updateMaxByType();
            });

            handleDrag(minThumb, true);
            handleDrag(maxThumb, false);

            updateMaxByType();

            $(window).on("resize", updateSlider);

            wrapper.closest("form").on("reset", function () {
                setTimeout(() => {
                    minValue = sliderMin;
                    updateMaxByType();
                }, 10);
            });

        });
    });
</script>
<script>
    var chartEl = document.querySelector("#chart");
    if (chartEl) {
        var options = {
            series: [{
                name: 'Sales',
                data: [4,3,10,9,29,19,22,9,12,7,19,5,13,9,17,2,7,5]
            }],
            chart: {
                height: 350,
                type: 'line',
                toolbar: {
                    show: false
                }
            },

            stroke: {
                width: 2,
                curve: 'smooth'
            },

            colors: ['#1455AC'],

            xaxis: {
                type: 'datetime',
                categories: [
                    '1/11/2000','2/11/2000','3/11/2000','4/11/2000','5/11/2000','6/11/2000',
                    '7/11/2000','8/11/2000','9/11/2000','10/11/2000','11/11/2000','12/11/2000',
                    '1/11/2001','2/11/2001','3/11/2001','4/11/2001','5/11/2001','6/11/2001'
                ],
                tickAmount: 10,
                labels: {
                    formatter: function(value, timestamp, opts) {
                        return opts.dateFormatter(new Date(timestamp), 'dd MMM')
                    }
                }
            },

            title: {
                text: 'Sales Trend',
                align: 'left',
                style: {
                    fontSize: "16px",
                    color: '#666'
                }
            }
        };
        var chart = new ApexCharts(chartEl, options);
        chart.render();
    }
</script>

<script>
    $(document).on("click", ".view_btn", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        let $card = $(this).closest(".upload-file");
        let $img = $card.find("img.upload-file-img");

        let actualSrc = $img.attr("src") || $img.attr("data-src") || $img.attr("data-default-src");

        if (actualSrc && actualSrc.length > 5) {
            let modalElement = document.getElementById('imageModal');

            if (modalElement) {
                $(modalElement).find(".imageModal_img").attr("src", actualSrc);
                $(modalElement).find(".download_btn").attr("href", actualSrc);

                try {
                    let myModal = new bootstrap.Modal(modalElement);
                    myModal.show();
                } catch (err) {
                    $(modalElement).modal('show');
                }
            }
        } else {
            console.error("Image source not found!");
        }
    });
    $(document).on('click', '.auction_remove', function () {
        localStorage.setItem('auction_badge_hidden_until', String(Date.now() + 5 * 60 * 1000));
        $(this).closest('.auction__badge').hide();
    });
</script>
<script src="{{ theme_asset('assets/js/custom.js') }}"></script>
