<?php

namespace App\Http\Controllers;

use App\Validators\Request\Route;
use App\Models\Category;
use App\Models\ItemCategory;
use App\Models\Transformers\ItemCategory as ItemCategoryTransformer;
use App\Utilities\Response as UtilityResponse;
use App\Validators\Request\Fields\ItemCategory as ItemCategoryValidator;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manage the category for an item row
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2019
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class ItemCategoryController extends Controller
{
    /**
     * Return the category assigned to an item
     *
     * @param Request $request
     * @param string $resource_type_id
     * @param string $resource_id
     * @param string $item_id
     *
     * @return JsonResponse
     */
    public function index(Request $request, string $resource_type_id, string $resource_id, string $item_id): JsonResponse
    {
        Route::itemRoute($resource_type_id, $resource_id, $item_id);

        $item_category = (new ItemCategory())->paginatedCollection(
            $resource_type_id,
            $resource_id,
            $item_id
        );

        if ($item_category === null) {
            UtilityResponse::notFound(trans('entities.item-category'));
        }

        $headers = [
            'X-Total-Count' => 1
        ];

        return response()->json(
            [(new ItemCategoryTransformer($item_category))->toArray()],
            200,
            $headers
        );
    }

    /**
     * Return a single item
     *
     * @param Request $request
     * @param string $resource_id
     * @param string $resource_type_id
     * @param string $item_id
     * @param string $item_category_id
     *
     * @return JsonResponse
     */
    public function show(
        Request $request,
        string $resource_type_id,
        string $resource_id,
        string $item_id,
        string $item_category_id
    ): JsonResponse
    {
        Route::itemRoute($resource_type_id, $resource_id, $item_id);

        if ($item_category_id === 'nill') {
            UtilityResponse::notFound(trans('entities.item-category'));
        }

        $item_category = (new ItemCategory())->single(
            $resource_type_id,
            $resource_id,
            $item_id,
            $item_category_id
        );

        if ($item_category === null) {
            UtilityResponse::notFound(trans('entities.item-category'));
        }

        $headers = [
            'X-Total-Count' => 1
        ];

        return response()->json(
            (new ItemCategoryTransformer($item_category))->toArray(),
            200,
            $headers
        );
    }

    /**
     * Generate the OPTIONS request for the item list
     *
     * @param Request $request
     * @param string $resource_type_id
     * @param string $resource_id
     * @param string $item_id
     *
     * @return JsonResponse
     */
    public function optionsIndex(Request $request, string $resource_type_id, string $resource_id, string $item_id): JsonResponse
    {
        Route::itemRoute($resource_type_id, $resource_id, $item_id);

        return $this->generateOptionsForIndex(
            [
                'description_localisation_string' => 'route-descriptions.item_category_GET_index',
                'parameters_config_string' => 'api.item-category.parameters.collection',
                'conditionals_config' => [],
                'sortable_config' => null,
                'searchable_config' => null,
                'enable_pagination' => false,
                'authentication_required' => false
            ],
            [
                'description_localisation_string' => 'route-descriptions.item_category_POST',
                'fields_config' => 'api.item-category.fields',
                'conditionals_config' => $this->conditionalPostParameters($resource_type_id),
                'authentication_required' => true
            ]
        );
    }

    /**
     * Generate the OPTIONS request for a specific item
     *
     * @param Request $request
     * @param string $resource_id
     * @param string $resource_type_id
     * @param string $item_id
     * @param string $item_category_id
     *
     * @return JsonResponse
     */
    public function optionsShow(
        Request $request,
        string $resource_type_id,
        string $resource_id,
        string $item_id,
        string $item_category_id
    ): JsonResponse
    {
        Route::itemRoute($resource_type_id, $resource_id, $item_id);

        if ($item_category_id === 'nill') {
            UtilityResponse::notFound(trans('entities.item-category'));
        }

        $item_category = (new ItemCategory())->single(
            $resource_type_id,
            $resource_id,
            $item_id,
            $item_category_id
        );

        if ($item_category === null) {
            UtilityResponse::notFound(trans('entities.item-category'));
        }

        return $this->generateOptionsForShow(
            [
                'description_localisation_string' => 'route-descriptions.item_category_GET_show',
                'parameters_config_string' => 'api.item-category.parameters.item',
                'conditionals_config' => [],
                'authentication_required' => false
            ],
            [
                'description_localisation_string' => 'route-descriptions.item_category_DELETE',
                'authentication_required' => true
            ]
        );
    }

    /**
     * Assign the category
     *
     * @param Request $request
     * @param string $resource_type_id
     * @param string $resource_id
     * @param string $item_id
     *
     * @return JsonResponse
     */
    public function create(
        Request $request,
        string $resource_type_id,
        string $resource_id,
        string $item_id
    ): JsonResponse
    {
        Route::itemRoute($resource_type_id, $resource_id, $item_id);

        $validator = (new ItemCategoryValidator)->create();

        if ($validator->fails() === true) {
            return $this->returnValidationErrors($validator, $this->conditionalPostParameters($resource_type_id));
        }

        try {
            $category_id = $this->hash->decode('category', $request->input('category_id'));

            if ($category_id === false) {
                UtilityResponse::unableToDecode();
            }

            $item_category = new ItemCategory([
                'item_id' => $item_id,
                'category_id' => $category_id
            ]);
            $item_category->save();
        } catch (Exception $e) {
            UtilityResponse::failedToSaveModelForCreate();
        }

        return response()->json(
            (new ItemCategoryTransformer($item_category))->toArray(),
            201
        );
    }

    /**
     * Generate any conditional POST parameters, will be merged with the relevant
     * config/api/[type]/fields.php data array
     *
     * @param integer $resource_type_id
     *
     * @return array
     */
    private function conditionalPostParameters($resource_type_id): array
    {
        $categories = (new Category())->categoriesByResourceType($resource_type_id);

        $conditional_post_parameters = ['category_id' => []];
        foreach ($categories as $category) {
            $id = $this->hash->encode('category', $category['category_id']);

            if ($id === false) {
                UtilityResponse::unableToDecode();
            }

            $conditional_post_parameters['category_id']['allowed_values'][$id] = [
                'value' => $id,
                'name' => $category['category_name'],
                'description' => $category['category_description']
            ];
        }

        return $conditional_post_parameters;
    }

    /**
     * Delete the assigned category
     *
     * @param Request $request,
     * @param string $resource_type_id,
     * @param string $resource_id,
     * @param string $item_id,
     * @param string $item_category_id
     *
     * @return JsonResponse
     */
    public function delete(
        Request $request,
        string $resource_type_id,
        string $resource_id,
        string $item_id,
        string $item_category_id
    ): JsonResponse
    {
        Route::itemCategory($resource_type_id, $resource_id, $item_id, $item_category_id);

        $item_category = (new ItemCategory())->single(
            $resource_type_id,
            $resource_id,
            $item_id,
            $item_category_id
        );

        if ($item_category === null) {
            UtilityResponse::notFound(trans('entities.item-category'));
        }


        try {
            $item_category->delete();

            UtilityResponse::successNoContent();
        } catch (QueryException $e) {
            UtilityResponse::foreignKeyConstraintError();
        } catch (Exception $e) {
            UtilityResponse::notFound(trans('entities.item-category'));
        }
    }
}
