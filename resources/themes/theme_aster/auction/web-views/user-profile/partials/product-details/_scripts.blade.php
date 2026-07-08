<script src="{{ dynamicAsset(path: 'public/assets/front-end/auction/js/auction-product-details.js') }}"></script>
<script>
    // Bidding History — "My Bids Only" filter
    $(document).on('change', '#myBidClaimedOnly', function () {
        var $container = $('#claimed-bid-list-container');
        var $insightContainer = $('#claimed-insight-container');
        var baseUrl = $container.data('url');
        var errorMessage = $container.data('error-message');
        var myBidsOnly = this.checked;

        var minHeight = $container.height();
        if (minHeight > 0) { $container.css('min-height', minHeight + 'px'); }
        $container.stop(true, false).animate({ opacity: 0 }, 150, function () {
            $container.html('<div class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></div>').animate({ opacity: 1 }, 150);
        });
        if ($insightContainer.length) {
            $insightContainer.stop(true, false).animate({ opacity: 0 }, 150);
        }

        $.ajax({
            url: baseUrl,
            type: 'GET',
            data: myBidsOnly ? { my_bids_only: 1 } : {},
            success: function (data) {
                $container.stop(true, false).hide().css('opacity', 1).html(data.html).fadeIn(500, function () {
                    $container.css('min-height', '');
                });
                if ($insightContainer.length && data.insightHtml) {
                    $insightContainer.stop(true, false).hide().css('opacity', 1).html(data.insightHtml).fadeIn(500, function () {
                        $insightContainer.css('min-height', '');
                    });
                    $insightContainer.find('[data-bs-toggle="tooltip"]').each(function () {
                        new bootstrap.Tooltip(this);
                    });
                }
            },
            error: function () {
                $container.stop(true, false).hide().css('opacity', 1).html('<div class="text-center py-4 text-danger">' + errorMessage + '</div>').fadeIn(500, function () {
                    $container.css('min-height', '');
                });
                if ($insightContainer.length) {
                    $insightContainer.stop(true, false).animate({ opacity: 1 }, 500, function () {
                        $insightContainer.css('min-height', '');
                    });
                }
            }
        });
    });

    // Delivery Status Update
    $(document).on('change', '.js-owner-delivery-status', function () {
        const select = $(this);
        const newValue = select.val();
        const currentValue = select.data('current-status');
        const auctionProductId = select.data('auction-product-id');
        const updateUrl = select.data('update-url');

        if (newValue === currentValue) return;

        Swal.fire({
            title: '{{ translate('are_you_sure') }}?',
            text: '{{ translate('Do you want to update the delivery status?') }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#377DFF',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ translate('Yes, Update') }}',
            cancelButtonText: '{{ translate('Cancel') }}',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) {
                select.val(currentValue);
                return;
            }
            select.prop('disabled', true);
            $.ajax({
                url: updateUrl,
                method: 'POST',
                data: {
                    _token: $('meta[name="_token"]').attr('content'),
                    auction_product_id: auctionProductId,
                    delivery_status: newValue,
                },
                success: function (response) {
                    toastr.success(response.message);
                    setTimeout(function () { location.reload(); }, 800);
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message ?? '{{ translate('Something went wrong') }}';
                    toastr.error(message);
                    select.val(currentValue).prop('disabled', false);
                },
            });
        });
    });

    // Payment Status Update (owner perspective — locks once paid)
    $(document).on('change', '.js-owner-payment-status', function () {
        const select = $(this);
        const newValue = select.val();
        const currentValue = select.data('current-status');
        const auctionProductId = select.data('auction-product-id');
        const updateUrl = select.data('update-url');

        if (!newValue || newValue === currentValue) return;

        Swal.fire({
            title: '{{ translate('are_you_sure') }}?',
            text: '{{ translate('Do you want to update the payment status?') }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#377DFF',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ translate('Yes, Update') }}',
            cancelButtonText: '{{ translate('Cancel') }}',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) {
                select.val(currentValue);
                return;
            }
            select.prop('disabled', true);
            $.ajax({
                url: updateUrl,
                method: 'POST',
                data: {
                    _token: $('meta[name="_token"]').attr('content'),
                    auction_product_id: auctionProductId,
                    payment_status: newValue,
                },
                success: function (response) {
                    toastr.success(response.message);
                    setTimeout(function () { location.reload(); }, 800);
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message ?? '{{ translate('Something went wrong') }}';
                    toastr.error(message);
                    select.val(currentValue).prop('disabled', false);
                },
            });
        });
    });

    // Tracking URL form
    $(document).on('submit', '#js-tracking-url-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn  = $form.find('[type="submit"]');
        $btn.prop('disabled', true);
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function (response) {
                toastr.success(response.message ?? '{{ translate('tracking_url_updated_successfully') }}');
                // Reload so the newly rendered Track Order section becomes visible.
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? '{{ translate('Something went wrong') }}');
                $btn.prop('disabled', false);
            },
        });
    });

    // Withdraw form
    $(document).on('submit', '#auction-withdraw-form', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serializeArray().reduce(function (obj, item) {
            let val = item.value.replace(/[^0-9.]/g, '');
            obj[item.name] = isNaN(parseFloat(val)) ? item.value : val;
            return obj;
        }, {});
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: { ...formData, _token: $('meta[name="_token"]').attr('content') },
            success: function (response) {
                if (response.status === 1) {
                    toastr.success(response.message);
                    $('#auction-customer-withdraw-offcanvas').offcanvas('hide');
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                const message = xhr.responseJSON?.message ?? '{{ translate('Something went wrong') }}';
                toastr.error(message);
            },
        });
    });

    // Withdraw method selector
    $(document).on('change', '.js-auction-withdraw-method-select', function () {
        const methodId = $(this).val();
        const url = $(this).data('route');
        if (!methodId || !url) return;
        $.ajax({
            url: url,
            method: 'POST',
            data: { _token: $('meta[name="_token"]').attr('content'), method_id: methodId },
            success: function (response) {
                $('#auction-withdraw-dynamic-fields').html(response.html ?? '');
            },
            error: function () {},
        });
    });

    @if($isOwner ?? false)
    // Cancel Auction
    $(document).on('click', '.js-cancel-auction-btn', function () {
        const auctionId = $(this).data('auction-id');
        Swal.fire({
            title: '{{ translate('are_you_sure') }}?',
            text: '{{ translate('Do you want to cancel this auction?') }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ translate('Yes, Cancel') }}',
            cancelButtonText: '{{ translate('No') }}',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ route('auction.cancel') }}',
                method: 'POST',
                data: { _token: $('meta[name="_token"]').attr('content'), auction_product_id: auctionId },
                success: function (response) {
                    if (response.status === 1) {
                        toastr.success(response.message);
                        setTimeout(function () { window.location.reload();}, 800);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message ?? '{{ translate('Something went wrong') }}');
                },
            });
        });
    });
    @endif
</script>

<script>
    (function () {
        const $select = $('.js-owner-product-status');
        if (!$select.length) return;

        function revertTo($el, value) {
            $el.prop('checked', String(value) === String($el.data('active-value')));
        }

        $select.on('change', function () {
            const $el = $(this);
            const newStatus = String($el.is(':checked') ? $el.data('active-value') : $el.data('inactive-value'));
            const previousStatus = String($el.data('current-status'));

            if (newStatus === previousStatus) return;

            Swal.fire({
                title: '{{ translate('are_you_sure') }}?',
                text: '{{ translate('Do you want to update the auction status') }}?',
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#6c757d',
                confirmButtonColor: '#377DFF',
                cancelButtonText: '{{ translate('no') }}',
                confirmButtonText: '{{ translate('yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $el.prop('disabled', true);
                    $.ajax({
                        url: $el.data('update-url'),
                        type: 'POST',
                        dataType: 'json',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
                        data: {
                            auction_product_id: $el.data('auction-product-id'),
                            status: newStatus
                        }
                    }).done(function (response) {
                        toastr.success(response.message || '{{ translate('auction_status_updated_successfully') }}');
                        setTimeout(function () { window.location.reload(); }, 1000);
                    }).fail(function (xhr) {
                        const msg = xhr?.responseJSON?.message || '{{ translate('something_went_wrong') }}';
                        toastr.error(msg);
                        revertTo($el, previousStatus);
                        $el.prop('disabled', false);
                    });
                } else {
                    revertTo($el, previousStatus);
                }
            });
        });
    })();
</script>

@if($isOwner ?? false)
<form id="js-delete-auction-form" method="POST" action="{{ route('auction.delete') }}" class="d-none">
    @csrf
    <input type="hidden" name="auction_product_id" id="js-delete-auction-product-id" value="">
</form>
<script>
    (function () {
        document.querySelectorAll('.js-delete-auction-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const auctionId = this.dataset.auctionId;
                Swal.fire({
                    title: '{{ translate('Delete_Auction') }}',
                    text: '{{ translate('This_will_permanently_delete_the_auction_and_all_associated_data._This_action_cannot_be_undone.') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '{{ translate('Yes,_Delete') }}',
                    cancelButtonText: '{{ translate('Cancel') }}',
                    reverseButtons: true,
                }).then(function (result) {
                    if (result.isConfirmed) {
                        document.getElementById('js-delete-auction-product-id').value = auctionId;
                        document.getElementById('js-delete-auction-form').submit();
                    }
                });
            });
        });
    })();
</script>
@endif

<script>
    (function () {
        if (typeof Plyr === 'undefined') return;
        document.querySelectorAll('.js-auction-product-player').forEach(function (el) {
            new Plyr(el, {
                iconUrl: '{{ dynamicAsset(path: 'public/assets/backend/libs/plyr/plyr.svg') }}'
            });
        });
    })();
</script>

<script>
    (function () {
        // See Less / See More toggle for offline payment details card
        document.querySelectorAll('.js-offline-payment-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const wrap = this.closest('.card-body')?.querySelector('.offline-payment-details-wrap');
                if (!wrap) return;
                const isHidden = wrap.classList.toggle('d-none');
                this.textContent = isHidden
                    ? (this.dataset.more || '{{ translate('See More') }}')
                    : (this.dataset.less || '{{ translate('See Less') }}');
            });
        });

        // Edit offline payment — load method fields, pre-fill existing values, open modal
        function setOfflineFieldValue(wrap, name, value) {
            if (value === null || value === undefined) return false;
            const field = wrap.querySelector('[name="' + name + '"]');
            if (!field) return false;
            field.value = value;
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
            return true;
        }

        function prefillOfflinePayment(wrap, paymentInfo) {
            Object.entries(paymentInfo).forEach(function ([key, value]) {
                // Hidden method_id / method_name are already rendered server-side.
                if (key === 'method_id' || key === 'method_name') return;

                // Dynamic fields submitted as method_informations[<input>].
                if (key === 'method_informations' && value && typeof value === 'object') {
                    Object.entries(value).forEach(function ([fieldKey, fieldValue]) {
                        setOfflineFieldValue(wrap, 'method_informations[' + fieldKey + ']', fieldValue);
                    });
                    return;
                }

                // Payment note submitted as offline_payment[payment_note].
                if (key === 'payment_note') {
                    setOfflineFieldValue(wrap, 'offline_payment[payment_note]', value);
                    return;
                }

                // Fallback: nested objects → bracket notation, scalars → direct or offline_payment[].
                if (value && typeof value === 'object') {
                    Object.entries(value).forEach(function ([fieldKey, fieldValue]) {
                        setOfflineFieldValue(wrap, key + '[' + fieldKey + ']', fieldValue);
                    });
                } else if (!setOfflineFieldValue(wrap, key, value)) {
                    setOfflineFieldValue(wrap, 'offline_payment[' + key + ']', value);
                }
            });
        }

        document.querySelectorAll('.js-edit-offline-payment-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const methodId     = this.dataset.methodId;
                const amount       = this.dataset.amount;
                const routeUrl     = document.getElementById('route-pay-offline-method-list')?.dataset?.url;
                const offlineModal = document.getElementById('modal-auction-offline-payment');
                const fieldWrap    = document.getElementById('auction_offline_payment_field');
                const goBackBtn    = document.getElementById('auction-offline-go-back');
                if (!routeUrl || !methodId || !offlineModal || !fieldWrap) return;

                // Opened from My Submitted Info → Edit: no previous step, hide Go Back.
                if (goBackBtn) goBackBtn.classList.add('d-none');

                let paymentInfo = {};
                try { paymentInfo = JSON.parse(this.dataset.paymentInfo || '{}'); } catch (e) {}

                fieldWrap.innerHTML = '';
                $.ajax({
                    url: routeUrl + '?method_id=' + methodId + '&edit_due_amount=' + encodeURIComponent(amount || 0),
                    type: 'GET',
                    success: function (response) {
                        fieldWrap.innerHTML = response?.methodHtml || '';
                        prefillOfflinePayment(fieldWrap, paymentInfo);
                        new bootstrap.Modal(offlineModal).show();
                    }
                });
            });
        });
    })();
</script>
