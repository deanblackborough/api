<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Manage items
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class ItemController extends Controller
{
    /**
     * @param Request $request
     * @param string $resource_type_id
     * @param string $resource_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, string $resource_type_id, string $resource_id)
    {
        return response()->json(
            [
                'results' => [
                    ['item_id' => $this->hash->encode(1)],
                    ['item_id' => $this->hash->encode(2)],
                    ['item_id' => $this->hash->encode(3)]
                ]
            ],
            200
        );
    }

    /**
     * @param Request $request
     * @param string $resource_id
     * @param string $resource_type_id
     * @param string $item_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $resource_type_id, string $resource_id, string $item_id)
    {
        return response()->json(
            [
                'result' => [
                    'resource_type_id' => $resource_type_id,
                    'resource_id' => $resource_id,
                    'item_id' => $item_id
                ]
            ],
            200
        );
    }

    /**
     * @param Request $request
     * @param string $resource_type_id
     * @param string $resource_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function optionsIndex(Request $request, string $resource_type_id, string $resource_id)
    {
        $options_response = $this->optionsResponse(
            [
                'GET' => [
                    'description' => 'Return the items for the given resource',
                    'parameters' => []
                ]
            ]
        );

        return response()->json(
            $options_response['verbs'],
            $options_response['http_status_code'],
            $options_response['headers']
        );
    }

    /**
     * @param Request $request
     * @param string $resource_id
     * @param string $resource_type_id
     * @param string $item_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function optionsShow(Request $request, string $resource_type_id, string $resource_id, string $item_id)
    {
        $options_response = $this->optionsResponse(
            [
                'GET' => [
                    'description' => 'Return the requested item',
                    'parameters' => []
                ]
            ]
        );

        return response()->json(
            $options_response['verbs'],
            $options_response['http_status_code'],
            $options_response['headers']
        );
    }
}
