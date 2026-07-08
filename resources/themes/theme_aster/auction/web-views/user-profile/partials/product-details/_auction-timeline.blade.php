<div class="p-15px card border-0 shadow-sm rounded">
    <div class="fs-14 fw-semibold title-clr mb-3">{{ translate('Auction Timeline') }}</div>
    <?php
        $timelineEntries = ($auctionProduct->auctionEditHistory ?? collect())->sortByDesc('created_at');
        $timelineLabels  = [
            \Modules\Auction\app\Models\AuctionEditHistory::TYPE_CREATE   => translate('Auction Created'),
            \Modules\Auction\app\Models\AuctionEditHistory::TYPE_UPDATE   => translate('Auction Updated'),
            \Modules\Auction\app\Models\AuctionEditHistory::TYPE_APPROVED => translate('Auction Approved'),
            \Modules\Auction\app\Models\AuctionEditHistory::TYPE_DENIED   => translate('Auction Denied'),
        ];
    ?>
    @if($timelineEntries->isNotEmpty())
    <div class="d-flex flex-column gap-15px w-100">
        @foreach($timelineEntries as $entry)
            <?php
                $entryLabel  = $timelineLabels[$entry->type] ?? translate(ucwords(str_replace('_', ' ', $entry->type)));
                $entryDate   = $entry->created_at?->format('d M Y, h:i A') ?? translate('N/A');
            ?>
            <div class="d-flex flex-column gap-1 align-items-start w-100">
                <div class="fs-14 title-semidark">{{ $entryLabel }}</div>
                <h4 class="fs-14 m-0 title-clr fw-semibold">{{ $entryDate }}</h4>
            </div>
        @endforeach
    </div>
    @else
    <div class="d-flex flex-column gap-1 align-items-start w-100">
        <div class="fs-14 title-semidark">{{ translate('Auction Created At') }}</div>
        <h4 class="fs-14 m-0 title-clr fw-semibold">{{ $auctionProduct?->created_at?->format('d M, Y, h:i A') ?? translate('N/A') }}</h4>
    </div>
    @endif
</div>
