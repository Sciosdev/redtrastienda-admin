@php
    $showMessageButton ??= false;
@endphp
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex gap-3 align-items-center justify-content-between">
            <div class="d-flex gap-10px align-items-center">
                @if($ownerUrl)
                    <a href="{{ $ownerUrl }}" class="rounded w-50px h-50px min-w-50px overflow-hidden d-block rounded-circle flex-shrink-0">
                        <img src="{{ $ownerImage }}" alt="{{ $ownerName }}" class="w-100 h-100 object-cover">
                    </a>
                @else
                    <div class="rounded w-50px h-50px min-w-50px overflow-hidden rounded-circle flex-shrink-0">
                        <img src="{{ $ownerImage }}" alt="{{ $ownerName }}" class="w-100 h-100 object-cover">
                    </div>
                @endif
                <div>
                    @if($ownerUrl)
                        <a href="{{ $ownerUrl }}" class="text-decoration-none">
                            <h6 class="mb-1 fs-14 text-capitalize fw-semibold title-clr line--limit-1">{{ $ownerName }}</h6>
                        </a>
                    @else
                        <h6 class="mb-1 fs-14 text-capitalize fw-semibold title-clr line--limit-1">{{ $ownerName }}</h6>
                    @endif
                    <p class="m-0 fs-13 title-semidark">{{ $ownerLabel }}</p>
                </div>
            </div>
            @if($showMessageButton)
                <button type="button"
                        class="btn btn-primary py-2 fs-12 d-flex align-items-center gap-2 px-3"
                        data-bs-toggle="modal"
                        data-bs-target="#auction-owner-chat-modal">
                    <i class="fi fi-rr-comment fs-14"></i>
                </button>
            @endif
        </div>
    </div>
</div>
