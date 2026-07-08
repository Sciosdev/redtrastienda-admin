<div class="d-flex flex-column gap-2">
    @if(!empty($fields['method_name']))
        <div class="d-flex gap-2">
            <span class="fs-12 title-semidark minmax-xs-120px">{{ translate('Method_name') }}</span>
            <span class="fs-12 title-semidark">:</span>
            <span class="fs-12 title-clr">{{ $fields['method_name'] }}</span>
        </div>
    @endif
    @foreach($fields ?? [] as $key => $val)
        @if($key !== 'method_name')
            <div class="d-flex gap-2">
                <span class="fs-12 title-semidark minmax-xs-120px">{{ translate(str_replace('_', ' ', $key)) }}</span>
                <span class="fs-12 title-semidark">:</span>
                <span class="fs-12 title-clr">{{ $val }}</span>
            </div>
        @endif
    @endforeach
</div>
