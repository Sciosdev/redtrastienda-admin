<?php

namespace App\Http\Controllers\RestAPI\v3\seller;

use App\Contracts\Repositories\OpportunityRequestRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OpportunityRequestController extends Controller
{
    private const STATUSES = ['new', 'in_review', 'contacted', 'served', 'rejected'];

    public function __construct(
        private readonly OpportunityRequestRepositoryInterface $opportunityRequestRepo,
    )
    {
    }

    /**
     * Contact requests received by the provider (seller).
     */
    public function receivedRequests(Request $request): JsonResponse
    {
        $seller = $request->seller;

        $filters = ['seller_id' => $seller->id];
        if ($request->has('status') && in_array($request['status'], self::STATUSES)) {
            $filters['status'] = $request['status'];
        }

        $requestList = $this->opportunityRequestRepo->getListWhere(
            orderBy: ['updated_at' => 'desc'],
            filters: $filters,
            relations: ['product', 'customer'],
            dataLimit: $request['limit'] ?? 'all',
            offset: $request['offset']
        );

        $requestList->map(function ($data) {
            if ($data->product) {
                $data->product = Helpers::product_data_formatting($data->product, false);
            }
            return $data;
        });

        return response()->json([
            'data' => $request->has('limit') ? $requestList->items() : $requestList,
            'total_size' => $request->has('limit') ? $requestList->total() : count($requestList),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
        ], 200);
    }

    /**
     * Provider updates the status / response of a received request.
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'status' => 'required|in:' . implode(',', self::STATUSES),
            'provider_response' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $seller = $request->seller;

        $opportunityRequest = $this->opportunityRequestRepo->getFirstWhere(params: [
            'id' => $request['id'],
            'seller_id' => $seller->id,
        ]);
        if (!$opportunityRequest) {
            return response()->json(['errors' => [['code' => 'request', 'message' => translate('Request not found')]]], 404);
        }

        $this->opportunityRequestRepo->update($request['id'], [
            'status' => $request['status'],
            'provider_response' => $request['provider_response'],
        ]);

        return response()->json(['message' => translate('Request updated successfully')], 200);
    }
}
