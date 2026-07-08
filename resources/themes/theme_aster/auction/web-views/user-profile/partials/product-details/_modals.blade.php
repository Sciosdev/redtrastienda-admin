@if($auctionProduct?->auction_current_status === \Modules\Auction\app\Enums\AuctionStatus::LIVE && !$isOwner)
    @if(getWebConfig(name: 'auction_entry_fee_amount_status') && getWebConfig(name: 'auction_entry_fee_amount_value') > 0)
        @include("auction.web-views.product._participate-entry-info", [
            'entry_fee_redirect' => route('auction.profile-view.product-details', ['slug' => $auctionProduct->slug])
        ])
    @endif

    @include("auction.web-views.product.details._place-bid-modal", ['auctionProduct' => $auctionProduct])
@endif
@if($isMyAuctionClaimed && $claimDeliveryStatus === \Modules\Auction\app\Enums\DeliveryStatus::DELIVERED && $deliveredBreakdown && $claimPaymentMethod === 'cash_on_delivery')
    @include("auction.web-views.product.details._cod-commission-modal", ['auctionProduct' => $auctionProduct, 'deliveredBreakdown' => $deliveredBreakdown])
@endif
@if($isMyAuctionClaimed && $claimDeliveryStatus === \Modules\Auction\app\Enums\DeliveryStatus::DELIVERED && $deliveredBreakdown && $claimPaymentMethod !== 'cash_on_delivery')
    @include("auction.web-views.partials._customer-withdraw-offcanvas", ['auctionProduct' => $auctionProduct, 'customerWithdrawalMethods' => $customerWithdrawalMethods, 'customerWithdrawRequest' => $customerWithdrawRequest, 'deliveredBreakdown' => $deliveredBreakdown])
@endif

@if(!$isOwner && in_array($auctionProduct->owner_type, ['seller', 'admin']))
    @php
        $chatVendorId   = $auctionProduct->owner_type === 'admin' ? 0 : ($auctionProduct->seller?->id ?? 0);
        $chatTargetName = $auctionProduct->owner_type === 'admin'
            ? getInHouseShopConfig(key: 'name')
            : ($auctionProduct->seller?->shop?->name ?? translate('Seller'));
    @endphp
    <div class="modal fade" id="auction-owner-chat-modal" tabindex="-1" aria-labelledby="auctionOwnerChatModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title text-capitalize" id="auctionOwnerChatModalLabel">
                        {{ translate('Send Message to') }} {{ $chatTargetName }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <form id="auction-owner-chat-form">
                        @csrf
                        <input type="hidden" name="vendor_id" value="{{ $chatVendorId }}">
                        <textarea name="message" class="form-control min-height-100px max-height-200px" required
                                  placeholder="{{ translate('Write here') }}..."></textarea>
                        <div class="d-flex justify-content-end gap-2 mt-3 align-items-center">
                            <a href="{{ route('chat', ['type' => 'vendor']) }}" class="text-primary btn-light btn fs-14">
                                {{ translate('Go To Chatbox') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm">{{ translate('Send') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('script')
    <script>
        $(document).on('submit', '#auction-owner-chat-form', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn  = $form.find('[type="submit"]');
            $btn.prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: $('#route-messages-store').data('url'),
                data: $form.serialize(),
                success: function () {
                    toastr.success('{{ translate('Message sent successfully') }}');
                    $form.trigger('reset');
                    $('#auction-owner-chat-modal').modal('hide');
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message ?? '{{ translate('Something went wrong') }}');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                },
            });
        });
    </script>
    @endpush
@endif
