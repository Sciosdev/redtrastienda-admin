<span id="route-auction-saved-products-toggle" data-route="{{ route('auction.saved-products.toggle') }}"></span>
<span id="route-messages-store" data-url="{{ route('messages') }}"></span>
<span id="message-send-successfully" data-text="{{ translate('send_successfully') }}"></span>
<span id="route-pay-offline-method-list" data-url="{{ route('auction.pay-offline-method-list') }}"></span>

@include('theme-views.layouts.partials._translate-text-for-js')
@include('theme-views.layouts.partials._route-for-js')
@include('theme-views.layouts.main-script')
@include('theme-views.layouts._firebase-script')

<script src="{{ theme_asset('assets/auction/js/auction-products.js') }}"></script>
<script src="{{ theme_asset('assets/auction/plugin/owl-carousel/owl.carousel.min.js') }}"></script>

<script>
    $(document).ready(function () {
        $(".product-slider").each(function () {
            var $wrapper = $(this);
            var $big = $wrapper.find(".big-slider");

            $big.owlCarousel({ items: 1, loop: false, nav: true, dots: false });

            function initZoom() {
                $big.find(".easyzoom").each(function () {
                    var api = $(this).data("easyZoom");
                    if (api) { api.teardown(); $(this).removeData("easyZoom"); }
                });
                $big.find(".owl-item.active").not(".cloned").find(".easyzoom").easyZoom();
            }

            setTimeout(function () { initZoom(); }, 200);
            $big.on("changed.owl.carousel", function () { setTimeout(initZoom, 100); });
        });
    });
</script>

{{-- Bootstrap 5 jQuery modal compatibility shim for shared auction JS files --}}
<script>
    (function () {
        if (typeof $ === 'undefined' || typeof bootstrap === 'undefined') return;

        $.fn.modal = function (action) {
            return this.each(function () {
                var elId = this.id;
                var targetEl = this;

                // Map BS4-era login alert modal to Aster's loginModal
                if (elId === 'login-alert-modal') {
                    targetEl = document.getElementById('loginModal') || this;
                }

                var instance = bootstrap.Modal.getOrCreateInstance(targetEl);
                if (action === 'show') instance.show();
                else if (action === 'hide') instance.hide();
                else if (action === 'toggle') instance.toggle();
                else if (action === 'dispose') instance.dispose();
            });
        };
    })();
</script>

<script src="{{ dynamicAsset(path: 'public/assets/front-end/auction/js/auction-common.js') }}"></script>

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

            if (!slider.length) return;

            let sliderMin = slider.data('min-value') ?? 0;
            let sliderMax = slider.data('max-value') ?? 100000;
            let minValue = sliderMin;
            let maxValue = sliderMax;
            let isRtl = $('html').attr('dir') === 'rtl';

            function clamp(value, min, max) { return Math.min(Math.max(value, min), max); }

            function updateSlider(syncInputs = true) {
                let sliderWidth = slider.width();
                let minLeft = ((minValue - sliderMin) / (sliderMax - sliderMin)) * sliderWidth;
                let maxLeft = ((maxValue - sliderMin) / (sliderMax - sliderMin)) * sliderWidth;
                if (isRtl) { minLeft = sliderWidth - minLeft; maxLeft = sliderWidth - maxLeft; }
                minThumb.css(isRtl ? "right" : "left", minLeft + "px");
                maxThumb.css(isRtl ? "right" : "left", maxLeft + "px");
                range.css({ [isRtl ? 'right' : 'left']: Math.min(minLeft, maxLeft) + "px", width: Math.abs(maxLeft - minLeft) + "px" });
                if (syncInputs) {
                    minInput.val(minValue);
                    maxInput.val(maxValue);
                }
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
                        let newValue = clamp(Math.round((startValue + (deltaX / sliderWidth) * (sliderMax - sliderMin)) * 100) / 100, sliderMin, sliderMax);
                        if (isMinThumb) { minValue = Math.min(newValue, maxValue); } else { maxValue = Math.max(newValue, minValue); }
                        updateSlider();
                    });
                    $(document).on("mouseup.slider touchend.slider", function () { $(document).off(".slider"); });
                });
            }

            minInput.on("input", function () { let val = parseFloat($(this).val()); if (!isNaN(val)) { minValue = clamp(val, sliderMin, maxValue); updateSlider(false); } });
            maxInput.on("input", function () { let val = parseFloat($(this).val()); if (!isNaN(val)) { maxValue = clamp(val, minValue, sliderMax); updateSlider(false); } });

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

            function clamp(value, min, max) { return Math.min(Math.max(value, min), max); }

            function updateSlider(syncInputs = true) {
                let sliderWidth = slider.width();
                if (sliderMax === sliderMin) return;
                let percentMin = (minValue - sliderMin) / (sliderMax - sliderMin);
                let percentMax = (maxValue - sliderMin) / (sliderMax - sliderMin);
                let minLeft = percentMin * sliderWidth;
                let maxLeft = percentMax * sliderWidth;
                if (isRtl) { minLeft = sliderWidth - minLeft; maxLeft = sliderWidth - maxLeft; }
                minThumb.css(isRtl ? "insetInlineEnd" : "insetInlineStart", minLeft + "px");
                maxThumb.css(isRtl ? "insetInlineEnd" : "insetInlineStart", maxLeft + "px");
                range.css({ [isRtl ? 'insetInlineEnd' : 'insetInlineStart']: Math.min(minLeft, maxLeft) + "px", width: Math.abs(maxLeft - minLeft) + "px" });
                if (syncInputs) {
                    minInput.val(minValue);
                    maxInput.val(maxValue);
                }
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
                        let deltaX = isRtl ? (startX - pageX) : (pageX - startX);
                        let newValue = clamp(Math.round((startValue + (deltaX / sliderWidth) * (sliderMax - sliderMin)) * 100) / 100, sliderMin, sliderMax);
                        if (isMinThumb) { minValue = Math.min(newValue, maxValue); } else { maxValue = Math.max(newValue, minValue); }
                        updateSlider();
                    });
                    $(document).on("mouseup.slider touchend.slider", function () { $(document).off(".slider"); });
                });
            }

            minInput.on("input", function () { let val = parseFloat($(this).val()); if (!isNaN(val)) { minValue = clamp(val, sliderMin, maxValue); updateSlider(false); } });
            maxInput.on("input", function () { let val = parseFloat($(this).val()); if (!isNaN(val)) { maxValue = clamp(val, minValue, sliderMax); updateSlider(false); } });
            typeSelect.on("change", function () { updateMaxByType(); });

            handleDrag(minThumb, true);
            handleDrag(maxThumb, false);
            updateMaxByType();
            $(window).on("resize", updateSlider);
            wrapper.closest("form").on("reset", function () { setTimeout(function () { minValue = sliderMin; updateMaxByType(); }, 10); });
        });
    });
</script>

<script>
    window.placeholderImageUrl = document.getElementById('get-place-holder-image')?.dataset?.src || '';
</script>
<script>
    $('.js-daterangepicker').each(function () {
        const $input = $(this);
        const isPreviousDateAllowed = $input.hasClass('previous-date-true');
        const isPlaceholderMode = $input.hasClass('placeholder-mode-true');

        $input.daterangepicker({
            autoUpdateInput: !isPlaceholderMode,
            locale: {
                format: 'DD MMM YYYY'
            },
            minDate: !isPreviousDateAllowed ? moment() : false
        });

        if (isPlaceholderMode) {
            $input.attr('placeholder', 'DD MMM YYYY - DD MMM YYYY');
            $input.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('DD MMM YYYY') + ' - ' + picker.endDate.format('DD MMM YYYY'));
            });
            $input.on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
        }
    });
</script>

<script>
    $.fn.select2DynamicDisplay = function () {

        function updateDisplay($element) {
            var $rendered = $element
                .siblings(".select2-container")
                .find(".select2-selection--multiple")
                .find(".select2-selection__rendered");

            var $container = $rendered.parent();
            var containerWidth = $container.width();
            var totalWidth = 0;
            var itemsToShow = [];
            var remainingCount = 0;

            // Get selected items
            var selectedItems = $element.select2("data");

            // Temp container for width calculation
            var $tempContainer = $("<div>")
                .css({
                    display: "inline-block",
                    padding: "0 15px",
                    whiteSpace: "nowrap",
                    visibility: "hidden",
                })
                .appendTo($container);

            selectedItems.forEach(function (item) {
                var text = item.text || item.id || "";

                var $tempItem = $("<span>")
                    .text(text)
                    .css({
                        display: "inline-block",
                        padding: "0 12px",
                        whiteSpace: "nowrap",
                    })
                    .appendTo($tempContainer);

                var itemWidth = $tempItem.outerWidth(true);

                if (totalWidth + itemWidth <= containerWidth - 50) {
                    totalWidth += itemWidth;
                    itemsToShow.push(item);
                } else {
                    remainingCount = selectedItems.length - itemsToShow.length;
                }
            });

            $tempContainer.remove();

            // Build HTML
            var html = "";

            itemsToShow.forEach(function (item) {
                var text = item.text || item.id || "";

                html += `
                    <li class="name">
                        <span>${text}</span>
                        <span class="close-icon" data-id="${item.id}">
                            <i class="fi fi-rr-cross-small"></i>
                        </span>
                    </li>`;
            });

            if (remainingCount && remainingCount > 0) {
                html += `
                    <li class="ms-auto">
                        <div class="more">+${remainingCount}</div>
                    </li>`;
            }

            // ❌ IMPORTANT: DO NOT append search box manually
            $rendered.html(html);
        }

        return this.each(function () {
            var $this = $(this);

            // Initialize Select2
            $this.select2({
                tags: true,
                placeholder: "Select VAT Rate",
                width: "100%"
            });

            // Update on change
            $this.on("change", function () {
                updateDisplay($this);
            });

            // Initial load
            setTimeout(() => updateDisplay($this), 100);

            // Resize fix
            $(window).on("resize", function () {
                updateDisplay($this);
            });

            // Remove item click
            $(document).on("click", ".close-icon", function (e) {
                e.stopPropagation();

                var itemId = $(this).data("id");

                var $select = $(this)
                    .closest(".select2")
                    .siblings(".multiple-select2");

                var values = $select.val() || [];

                $select.val(values.filter(id => id != itemId)).trigger("change");
            });
        });
    };
    // Init
    $(document).ready(function () {
        $(".multiple-select2").select2DynamicDisplay();
    });
</script>

