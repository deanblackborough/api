<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Utilities\Response as UtilityResponse;
use App\Validators\Request\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manage items
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2019
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class ItemMoveController extends Controller
{
    protected $collection_parameters = [];
    protected $get_parameters = [];
    protected $pagination = [];

    public function move(
        Request $request,
        string $resource_type_id,
        string $resource_id,
        string $item_id
    ): JsonResponse
    {
        Route::itemRoute($resource_type_id, $resource_id, $item_id);

        return response()->json(
            [],
            201
        );
    }

    public function optionsMove(
        Request $request,
        string $resource_type_id,
        string $resource_id,
        string $item_id
    ): JsonResponse
    {
        Route::itemRoute($resource_type_id, $resource_id, $item_id);

        return $this->generateOptionsForIndex(
            [
                'description_localisation_string' => '',
                'parameters_config_string' => null,
                'conditionals_config' => null,
                'sortable_config' => null,
                'searchable_config' => null,
                'enable_pagination' => false,
                'authentication_required' => false
            ],
            [
                'description_localisation_string' => 'route-descriptions.item_move_POST',
                'fields_config' => 'api.item-move.fields',
                'conditionals_config' => $this->conditionalPostParameters(
                    $resource_type_id,
                    $resource_id
                ),
                'authentication_required' => true
            ]
        );
    }

    /**
     * Generate any conditional POST parameters, will be merged with the
     * relevant config/api/[type]/fields.php data array
     *
     * @param integer $resource_type_id
     * @param integer $resource_id
     *
     * @return array
     */
    private function conditionalPostParameters(
        int $resource_type_id,
        int $resource_id
    ): array
    {
        $resources = (new Resource())->resourcesForResourceType(
            $resource_type_id,
            $resource_id
        );

        $conditional_post_parameters = ['resource_id' => []];
        foreach ($resources as $resource) {
            $id = $this->hash->encode('resource', $resource['resource_id']);

            if ($id === false) {
                UtilityResponse::unableToDecode();
            }

            $conditional_post_parameters['resource_id']['allowed_values'][$id] = [
                'value' => $id,
                'name' => $resource['resource_name'],
                'description' => $resource['resource_description']
            ];
        }

        return $conditional_post_parameters;
    }
}
