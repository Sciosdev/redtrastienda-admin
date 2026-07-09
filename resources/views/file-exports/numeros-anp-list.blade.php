<html>
<table>
    <thead>
        <tr>
            <th style="font-size: 18px">{{ translate('numeros_ANP') }}</th>
        </tr>
        <tr>
            <th>{{ translate('total_ANP') . ' - ' . count($data['numeros']) }}</th>
        </tr>
        <tr>
            <th>{{ translate('search_Criteria') }} -</th>
            <th></th>
            <th>{{ translate('search_Bar_Content') . ' - ' . ($data['search'] ?? 'N/A') }}</th>
        </tr>
        <tr>
            <td>{{ translate('SL') }}</td>
            <td>{{ translate('numero_ANP') }}</td>
            <td>{{ translate('status') }}</td>
            <td>{{ translate('afiliado_asignado') }}</td>
            <td>{{ translate('fecha_generacion') }}</td>
            <td>{{ translate('fecha_activacion') }}</td>
            <td>{{ translate('operador') }}</td>
            <td>{{ translate('observaciones') }}</td>
        </tr>
        @foreach ($data['numeros'] as $key => $item)
            <tr>
                <td>{{ ++$key }}</td>
                <td>{{ $item['numero_anp'] }}</td>
                <td>{{ translate($item['estatus']) }}</td>
                <td>{{ $item?->afiliado?->f_name ? $item->afiliado->f_name . ' ' . $item->afiliado->l_name : '' }}</td>
                <td>{{ $item['fecha_generacion'] }}</td>
                <td>{{ $item['fecha_activacion'] }}</td>
                <td>{{ $item['operador'] }}</td>
                <td>{{ $item['observaciones'] }}</td>
            </tr>
        @endforeach
    </thead>
</table>
</html>
