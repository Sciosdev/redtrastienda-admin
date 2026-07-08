<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Contracts\Repositories\OpportunityRequestRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OpportunityRequestController extends Controller
{
    public function __construct(
        private readonly OpportunityRequestRepositoryInterface $opportunityRequestRepo,
        private readonly ProductRepositoryInterface            $productRepo,
    )
    {
    }

    /**
     * Affiliate requests contact for an opportunity (product).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $customer = Helpers::getCustomerInformation($request);

        $product = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']]);
        if (!$product) {
            return response()->json(['errors' => [['code' => 'product', 'message' => translate('Opportunity not found')]]], 404);
        }

        $existingRequest = $this->opportunityRequestRepo->getFirstWhere(params: [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
        ]);
        if ($existingRequest && in_array($existingRequest->status, ['new', 'in_review'])) {
            return response()->json(['message' => translate('You already have a pending request for this opportunity')], 200);
        }

        $this->opportunityRequestRepo->add([
            'product_id' => $product->id,
            'seller_id' => $product->added_by === 'seller' ? $product->user_id : null,
            'customer_id' => $customer->id,
            'comment' => $request['comment'],
            'status' => 'new',
        ]);

        return response()->json(['message' => translate('Contact request sent successfully')], 200);
    }

    /**
     * Affiliate's own contact requests.
     */
    public function myRequests(Request $request): JsonResponse
    {
        $customer = Helpers::getCustomerInformation($request);

        $requestList = $this->opportunityRequestRepo->getListWhere(
            orderBy: ['updated_at' => 'desc'],
            filters: ['customer_id' => $customer->id],
            relations: ['product', 'seller.shop'],
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
}
