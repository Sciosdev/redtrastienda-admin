<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\NumeroAnpRepositoryInterface;
use App\Exports\NumeroAnpListExport;
use App\Http\Controllers\BaseController;
use App\Imports\NumerosAnpImport;
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
