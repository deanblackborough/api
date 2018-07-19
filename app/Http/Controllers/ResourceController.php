<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Manage resources
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class ResourceController extends Controller
{
    /**
     * @param string $resource_type_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, string $resource_type_id)
    {
        return response()->json(
            [
                'results' => [
                    ['resource_id' => $this->hash->encode(1)],
                    ['resource_id' => $this->hash->encode(2)],
                    ['resource_id' => $this->hash->encode(3)]
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
    public function show(Request $request, string $resource_type_id, string $resource_id)
    {
        return response()->json(
            [
                'result' => [
                    'resource_type_id' => $resource_type_id,
                    'resource_id' => $resource_id
                ]
            ],
            200
        );
    }
}
