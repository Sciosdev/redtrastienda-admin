<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\NumeroAnpRepositoryInterface;
use App\Exports\NumeroAnpListExport;
use App\Http\Controllers\BaseController;
use App\Imports\AfiliadosImport;
use App\Imports\NumerosAnpImport;
use App\Services\AfiliadoPrecargaService;
use App\Services\NumeroAnpService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NumeroAnpController extends BaseController
{
    public function __construct(
        private readonly NumeroAnpRepositoryInterface $numeroAnpRepo,
        private readonly NumeroAnpService             $numeroAnpService,
        private readonly AfiliadoPrecargaService      $afiliadoPrecargaService,
    )
    {
    }

    public function index(Request|null $request, ?string $type = null): View
    {
        $numeros = $this->numeroAnpRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['estatus' => $request['estatus'] ?? ''],
            relations: ['afiliado'],
            dataLimit: getWebConfig(name: 'pagination_limit'),
        );
        $totalNumeros = $this->numeroAnpRepo->getList(dataLimit: 'all')->count();
        return view('admin-views.numeros-anp.list', compact('numeros', 'totalNumeros'));
    }

    public function generateBatch(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1|max:1000',
            'prefijo' => 'nullable|string|max:20',
        ]);
        if ($validator->fails()) {
            ToastMagic::error($validator->errors()->first());
            return back();
        }

        $generated = $this->numeroAnpService->generateBatch(
            cantidad: (int)$request['cantidad'],
            prefijo: $request['prefijo'],
            operador: auth('admin')->user()?->name,
        );

        ToastMagic::success($generated . ' ' . translate('numeros_ANP_generados'));
        return back();
    }

    public function import(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'anp_file' => 'required|file|mimes:csv,xlsx,xls,txt',
        ]);
        if ($validator->fails()) {
            ToastMagic::error($validator->errors()->first());
            return back();
        }

        try {
            $import = new NumerosAnpImport();
            Excel::import($import, $request->file('anp_file'));
        } catch (\Throwable $exception) {
            ToastMagic::error(translate('you_have_uploaded_a_wrong_format_file'));
            return back();
        }

        $result = $this->numeroAnpService->importNumeros(parsedRows: $import->rows);
        ToastMagic::success(translate('importados') . ': ' . $result['imported'] . ' | ' . translate('saltados') . ': ' . $result['skipped']);
        return back();
    }

    /**
     * R-Precarga: import del Excel COMPLETO de ANPEC (usuario + perfil + número
     * por fila). El import simple de números de arriba se conserva tal cual.
     */
    public function importAfiliados(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'afiliados_file' => 'required|file|mimes:csv,xlsx,xls,txt',
        ]);
        if ($validator->fails()) {
            ToastMagic::error($validator->errors()->first());
            return back();
        }

        // 18.5k filas en una sola request: ampliar el límite si el hosting lo
        // permite. Plan B documentado: partir el archivo (import idempotente).
        @set_time_limit(600);

        $import = new AfiliadosImport(precargaService: $this->afiliadoPrecargaService);
        try {
            Excel::import($import, $request->file('afiliados_file'));
        } catch (\Throwable $exception) {
            ToastMagic::error(translate('you_have_uploaded_a_wrong_format_file'));
            return back();
        }

        $r = $import->result;
        ToastMagic::success(
            translate('afiliados_creados') . ': ' . $r['creados']
            . ' | ' . translate('actualizados') . ': ' . $r['actualizados']
            . ' | ' . translate('sin_cambios') . ': ' . $r['sin_cambios']
            . ' | ' . translate('saltados_por_reclamada') . ': ' . $r['saltados_reclamada']
            . ' | ' . translate('saltados_por_bloqueado') . ': ' . $r['saltados_bloqueado']
            . ' | ' . translate('saltados_por_anomalia') . ': ' . $r['saltados_anomalia']
            . ' | ' . translate('saltados_por_correo_duplicado') . ': ' . $r['saltados_email_duplicado']
            . ' | ' . translate('saltados_sin_patron_anp') . ': ' . $r['saltados_sin_patron']
        );
        return back();
    }

    /**
     * R-Precarga: alta manual individual (pedida por ANPEC). Mismo resultado
     * que una fila del import: usuario + perfil + número ligado.
     */
    public function storeAfiliadoManual(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'numero_anp' => ['required', 'string', 'max:50', 'regex:' . AfiliadosImport::PATRON_NUMERO_ANP],
            'nombre' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'nombre_negocio' => 'nullable|string|max:191',
            'direccion' => 'nullable|string|max:191',
            'estado' => 'nullable|string|max:100',
        ], [
            'numero_anp.regex' => translate('el_numero_ANP_debe_ser_ANP_mas_digitos_y_letra_final_opcional'),
        ]);
        if ($validator->fails()) {
            ToastMagic::error($validator->errors()->first());
            return back();
        }

        $resultado = $this->afiliadoPrecargaService->altaManual(
            datos: $request->only(['numero_anp', 'nombre', 'telefono', 'email', 'nombre_negocio', 'direccion', 'estado']),
            operador: auth('admin')->user()?->name,
        );

        $resultado['ok'] ? ToastMagic::success($resultado['message']) : ToastMagic::error($resultado['message']);
        return back();
    }

    public function export(Request $request): BinaryFileResponse
    {
        $numeros = $this->numeroAnpRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['estatus' => $request['estatus'] ?? ''],
            relations: ['afiliado'],
            dataLimit: 'all',
        );
        $data = [
            'numeros' => $numeros,
            'search' => $request['searchValue'],
        ];
        return Excel::download(new NumeroAnpListExport($data), 'Numeros-ANP.xlsx');
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $numero = $this->numeroAnpRepo->getFirstWhere(params: ['id' => $request['id']]);
        if (!$numero) {
            ToastMagic::error(translate('numero_ANP_no_encontrado'));
            return back();
        }
        if ($numero->estatus === 'usado') {
            ToastMagic::error(translate('no_se_puede_modificar_un_numero_ANP_usado'));
            return back();
        }

        $estatus = $request['estatus'];
        if (!in_array($estatus, ['disponible', 'bloqueado', 'cancelado'])) {
            ToastMagic::error(translate('estatus_invalido'));
            return back();
        }
        if ($estatus === 'cancelado' && $numero->estatus !== 'disponible') {
            ToastMagic::error(translate('solo_se_puede_cancelar_un_numero_ANP_disponible'));
            return back();
        }

        $this->numeroAnpRepo->update(id: $numero->id, data: ['estatus' => $estatus]);
        ToastMagic::success(translate('estatus_actualizado_correctamente'));
        return back();
    }
}
