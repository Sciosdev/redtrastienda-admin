<div>
    <div class="price-range-wrapper">
        <div class="d-flex gap-2 justify-content-between align-items-center fllex-wrap mb-3">
            <h6 class="fs-13 fw-semibold mb-0">{{ translate('Auction Ending Time') }}</h6>
            <div>
                <select name="" id="" class="form-select min-w-70px h-30px fs-11 duration_time_type">
                    <option value="min" selected>{{ translate('Minutes') }}</option>
                    <option value="hour">{{ translate('Hours') }}</option>
                    <option value="day">{{ translate('Day') }}</option>
                </select>
            </div>
        </div>
        <div class="d-flex align-items-end flex-wrap flex-sm-nowrap gap-2">
            <div class="d-flex align-items-end gap-2">
                <div class="form-group mb-0">
                    <label for="duration_min" class="mb-1 fs-10">{{ translate('Min') }}</label>
                    <input type="number" id="duration_min" class="form-control border py-1 fs-12 title-clr min-h-30px duration_min" name="ending_time"
                           placeholder="{{ translate('ex: 0') }}" value="0" min="0">
                </div>
                <div class="mb-2">-</div>
                <div class="form-group mb-0">
                    <label for="duration_max" class="mb-1 fs-10">{{ translate('Max') }}</label>
                    <input type="number" id="duration_max" class="form-control border py-1 fs-12 title-clr min-h-30px duration_max"
                           name="ending_time" value="60" min="0"
                           placeholder="{{ translate('ex: 60') }}">
                </div>
                <button class="btn btn-primary p-1 border-0 w-30px h-30px min-w-30px fs-12 action-search-products-by-price" id="">
                    <i class="fi fi-rr-angle-right"></i>
                </button>
            </div>
        </div>

        <div class="duration_range_slider range_slider_div mt-3 mb-1 rounded-10"
             data-max-min="60"
             data-max-hour="24"
             data-max-day="7"
             data-min-value="0"
             data-max-value="1000"
        >
            <div class="slider-range duration_slider_range"></div>
            <div class="slider-thumb duration_thumb_min" data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="{{ translate('Min') }}"></div>
            <div class="slider-thumb duration_thumb_max" data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="{{ translate('Max') }}"></div>
        </div>
    </div>
</div>
