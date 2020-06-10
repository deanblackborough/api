<?php

namespace App\Http\Controllers;

use App\Item\Factory;
use App\Models\ItemTransfer;
use App\Option\Delete;
use App\Option\Get;
use App\Option\Patch;
use App\Option\Post;
use App\Response\Header\Header;
use App\Utilities\RoutePermission;
use App\Validators\FilterParameters;
use App\Validators\Parameters;
use App\Validators\Validate;
use App\Models\Category;
use App\Models\Item;
use App\Models\Subcategory;
use App\Utilities\Pagination as UtilityPagination;
use App\Utilities\Request as UtilityRequest;
use App\Utilities\Response as UtilityResponse;
use App\Validators\SearchParameters;
use App\Validators\SortParameters;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Manage items
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2020
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class ItemController extends Controller
{
    /**
     * Return all the items for the resource type and resource applying
     * any filtering, pagination and ordering
     *
     * @param string $resource_type_id
     * @param string $resource_id
     *
     * @return JsonResponse
     */
    public function index(
        string $resource_type_id,
        string $resource_id
    ): JsonResponse
    {
        \App\Request\Route\Validate::resource(
            $resource_type_id,
            $resource_id,
            $this->permitted_resource_types,
        );

        $item_interface = Factory::item($resource_type_id);

        $parameters = Parameters::fetch(
            array_keys($item_interface->collectionParameters()),
            (int) $resource_type_id,
            (int) $resource_id
        );

        $search_parameters = SearchParameters::fetch(
            $item_interface->searchParameters()
        );

        $filter_parameters = FilterParameters::fetch(
            $item_interface->filterParameters()
        );

        $item_model = $item_interface->model();
        $total = $item_model->totalCount(
            $resource_type_id,
            $resource_id,
            $parameters,
            $search_parameters,
            $filter_parameters
        );

        $sort_parameters = SortParameters::fetch(
            $item_interface->sortParameters()
        );

        $pagination = UtilityPagination::init(request()->path(), $total)
            ->setParameters($parameters)
            ->setSortParameters($sort_parameters)
            ->setSearchParameters($search_parameters)
            ->paging();

        $items = $item_model->paginatedCollection(
            $resource_type_id,
            $resource_id,
            $pagination['offset'],
            $pagination['limit'],
            $parameters,
            $search_parameters,
            $filter_parameters,
            $sort_parameters
        );

        $headers = new Header();
        $headers->collection($pagination, count($items), $total);

        $filter_header = FilterParameters::xHeader();
        if ($filter_header !== null) {
            $headers->addFilter($filter_header);
        }

        $sort_header = SortParameters::xHeader();
        if ($sort_header !== null) {
            $headers->addSort($sort_header);
        }

        $search_header = SearchParameters::xHeader();
        if ($search_header !== null) {
            $headers->addSearch($search_header);
        }

        $parameters_header = Parameters::xHeader();
        if ($parameters_header !== null) {
            $headers->addParameters($parameters_header);
        }

        return response()->json(
            array_map(
                function($item) use ($item_interface) {
                    return $item_interface->transformer($item)->toArray();
                },
                $items
            ),
            200,
            $headers->headers()
        );
    }

    /**
     * Return a single item
     *
     * @param string $resource_id
     * @param string $resource_type_id
     * @param string $item_id
     *
     * @return JsonResponse
     */
    public function show(
        string $resource_type_id,
        string $resource_id,
        string $item_id
    ): JsonResponse
    {
        \App\Request\Route\Validate::item(
            $resource_type_id,
            $resource_id,
            $item_id,
            $this->permitted_resource_types
        );

        $item_interface = Factory::item($resource_type_id);

        $parameters = Parameters::fetch(
            array_keys($item_interface->showParameters()),
            (int) $resource_type_id,
            (int) $resource_id
        );

        $item_model = $item_interface->model();

        $item = $item_model->single(
            $resource_type_id,
            $resource_id,
            $item_id,
            $parameters
        );

        if ($item === null) {
            UtilityResponse::notFound(trans('entities.item'));
        }

        $headers = new Header();
        $headers->item();

        return response()->json(
            $item_interface->transformer($item)->toArray(),
            200,
            $headers->headers()
        );
    }

    /**
     * Generate the OPTIONS request for the item list
     *
     * @param string $resource_type_id
     * @param string $resource_id
     *
     * @return JsonResponse
     */
    public function optionsIndex(
        string $resource_type_id,
        string $resource_id
    ): JsonResponse
    {
        \App\Request\Route\Validate::resource(
            $resource_type_id,
            $resource_id,
            $this->permitted_resource_types,
        );

        $item_interface = Factory::item($resource_type_id);

        $permissions = RoutePermission::resource(
            $resource_type_id,
            $resource_id,
            $this->permitted_resource_types,
        );

        $defined_parameters = Parameters::fetch(
            array_keys($item_interface->collectionParameters()),
            (int) $resource_type_id,
            (int) $resource_id
        );

        $parameters_data = $this->parametersData(
            $resource_type_id,
            $resource_id,
            array_merge(
                $item_interface->collectionParametersNames(),
                $defined_parameters
            )
        );

        $get = Get::init()->
            setSortable($item_interface->sortParametersConfig())->
            setSearchable($item_interface->searchParametersConfig())->
            setFilterable($item_interface->filterParametersConfig())->
            setParameters($item_interface->collectionParametersConfig())->
            setParametersData($parameters_data)->
            setPagination(true)->
            setAuthenticationStatus($permissions['view'])->
            setDescription('route-descriptions.item_GET_index')->
            option();

        $post = Post::init()->
            setFields($item_interface->fieldsConfig())->
            setDescription( 'route-descriptions.item_POST')->
            setAuthenticationRequired(true)->
            setAuthenticationStatus($permissions['manage'])->
            option();

        return $this->optionsResponse(
            $get + $post,
            200
        );
    }

    /**
     * Generate the OPTIONS request for a specific item
     *
     * @param string $resource_id
     * @param string $resource_type_id
     * @param string $item_id
     *
     * @return JsonResponse
     */
    public function optionsShow(
        string $resource_type_id,
        string $resource_id,
        string $item_id
    ): JsonResponse
    {
        \App\Request\Route\Validate::item(
            $resource_type_id,
            $resource_id,
            $item_id,
            $this->permitted_resource_types
        );

        $permissions = RoutePermission::item(
            $resource_type_id,
            $resource_id,
            $item_id,
            $this->permitted_resource_types,
        );

        $item_interface = Factory::item($resource_type_id);

        $item_model = $item_interface->model();

        $item = $item_model->single($resource_type_id, $resource_id, $item_id);

        if ($item === null) {
            UtilityResponse::notFound(trans('entities.item'));
        }

        $get = Get::init()->
            setParameters($item_interface->showParametersConfig())->
            setAuthenticationStatus($permissions['view'])->
            setDescription('route-descriptions.item_GET_show')->
            option();

        $delete = Delete::init()->
            setDescription('route-descriptions.item_DELETE')->
            setAuthenticationStatus($permissions['manage'])->
            setAuthenticationRequired(true)->
            option();

        $patch = Patch::init()->
            setFields($item_interface->fieldsConfig())->
            setDescription('route-descriptions.item_PATCH')->
            setAuthenticationStatus($permissions['manage'])->
            setAuthenticationRequired(true)->
            option();

        return $this->optionsResponse(
            $get + $delete + $patch,
            200
        );
    }

    /**
     * Create a new item
     *
     * @param string $resource_type_id
     * @param string $resource_id
     *
     * @return JsonResponse
     */
    public function create(
        string $resource_type_id,
        string $resource_id
    ): JsonResponse
    {
        \App\Request\Route\Validate::resource(
            $resource_type_id,
            $resource_id,
            $this->permitted_resource_types,
            true
        );

        $item_interface = Factory::item($resource_type_id);

        $validator_factory = $item_interface->validator();
        $validator = $validator_factory->create();
        UtilityRequest::validateAndReturnErrors($validator);

        $model = $item_interface->model();

        try {
            $item = new Item([
                'resource_id' => $resource_id,
                'created_by' => Auth::user()->id
            ]);
            $item->save();

            $item_type = $item_interface->create((int) $item->id);

        } catch (Exception $e) {
            UtilityResponse::failedToSaveModelForCreate();
        }

        return response()->json(
            $item_interface->transformer($model->instanceToArray($item, $item_type))->toArray(),
            201
        );
    }

    /**
     * Update the selected item
     *
     * @param string $resource_type_id
     * @param string $resource_id
     * @param string $item_id
     *
     * @return JsonResponse
     */
    public function update(
        string $resource_type_id,
        string $resource_id,
        string $item_id
    ): JsonResponse
    {
        \App\Request\Route\Validate::item(
            $resource_type_id,
            $resource_id,
            $item_id,
            $this->permitted_resource_types,
            true
        );

        $item_interface = Factory::item($resource_type_id);

        UtilityRequest::checkForEmptyPatch();

        UtilityRequest::checkForInvalidFields($item_interface->validationPatchableFieldNames());

        $validator_factory = $item_interface->validator();
        $validator = $validator_factory->update();
        UtilityRequest::validateAndReturnErrors($validator);

        $item = (new Item())->instance($resource_type_id, $resource_id, $item_id);
        $item_type = $item_interface->instance((int) $item_id);

        if ($item === null || $item_type === null) {
            UtilityResponse::failedToSelectModelForUpdateOrDelete();
        }

        try {
            $item->updated_by = Auth::user()->id;

            if ($item->save() === true) {
                $item_interface->update(request()->all(), $item_type);
            }
        } catch (Exception $e) {
            UtilityResponse::failedToSaveModelForUpdate();
        }

        return UtilityResponse::successNoContent();
    }

    /**
     * Delete the assigned item
     *
     * @param string $resource_type_id,
     * @param string $resource_id,
     * @param string $item_id
     *
     * @return JsonResponse
     */
    public function delete(
        string $resource_type_id,
        string $resource_id,
        string $item_id
    ): JsonResponse
    {
        \App\Request\Route\Validate::resource(
            $resource_type_id,
            $resource_id,
            $this->permitted_resource_types,
            true
        );

        $item_interface = Factory::item($resource_type_id);

        $item_model = $item_interface->model();

        $item_type = $item_model->instance($item_id);
        $item = (new Item())->instance($resource_type_id, $resource_id, $item_id);

        if ($item === null || $item_type === null) {
            UtilityResponse::notFound(trans('entities.item'));
        }

        if (in_array($item_interface->type(), ['allocated-expense', 'simple-expense']) &&
            $item_model->hasCategoryAssignments($item_id) === true) {
                UtilityResponse::foreignKeyConstraintError();
        }

        try {
            (new ItemTransfer())->deleteTransfers($item_id);
            $item_type->delete();
            $item->delete();

            UtilityResponse::successNoContent();
        } catch (QueryException $e) {
            UtilityResponse::foreignKeyConstraintError();
        } catch (Exception $e) {
            UtilityResponse::notFound(trans('entities.item'), $e);
        }
    }

    /**
     * Set the allowed values for any conditional parameters, these will be
     * merged with the data arrays defined in config/api/[item-type]/parameters.php
     *
     * Checks to see if a parameter requiring conditional values exists, if it
     * does, populate the values
     *
     * @param integer $resource_type_id
     * @param integer $resource_id
     * @param array $parameters Merged array of definable parameters and any
     * set values
     *
     * @return array
     */
    private function parametersData(
        int $resource_type_id,
        int $resource_id,
        array $parameters
    ): array
    {

        $item_interface = Factory::item($resource_type_id);

        $conditional_parameters = [];

        if (array_key_exists('year', $parameters) === true) {
            $conditional_parameters['year']['allowed_values'] = [];

            for (
                $i = $item_interface->conditionalParameterMinYear($resource_id);
                $i <= $item_interface->conditionalParameterMaxYear($resource_id);
                $i++
            ) {
                $conditional_parameters['year']['allowed_values'][$i] = [
                    'value' => $i,
                    'name' => $i,
                    'description' => trans('item-type-' . $item_interface->type() .
                            '/allowed-values.description-prefix-year') . $i
                ];
            }
        }

        if (array_key_exists('month', $parameters) === true) {
            $conditional_parameters['month']['allowed_values'] = [];

            for ($i=1; $i < 13; $i++) {
                $conditional_parameters['month']['allowed_values'][$i] = [
                    'value' => $i,
                    'name' => date("F", mktime(0, 0, 0, $i, 10)),
                    'description' => trans('item-type-' . $item_interface->type() .
                        '/allowed-values.description-prefix-month') .
                        date("F", mktime(0, 0, 0, $i, 1))
                ];
            }
        }

        if (array_key_exists('category', $parameters) === true) {
            $conditional_parameters['category']['allowed_values'] = [];

            $categories = (new Category())->paginatedCollection(
                $resource_type_id,
                $this->permitted_resource_types,
                $this->include_public,
                0,
                100
            );

            foreach ($categories as $category) {
                $conditional_parameters['category']['allowed_values'][$this->hash->encode('category', $category['category_id'])] = [
                    'value' => $this->hash->encode('category', $category['category_id']),
                    'name' => $category['category_name'],
                    'description' => trans('item-type-' . $item_interface->type() .
                            '/allowed-values.description-prefix-category') .
                        $category['category_name'] .
                        trans('item-type-' . $item_interface->type() .
                            '/allowed-values.description-suffix-category')
                ];
            }
        }

        if (
            array_key_exists('category', $parameters) === true &&
            $parameters['category'] !== null &&
            array_key_exists('subcategory', $parameters) === true
        ) {
            $conditional_parameters['subcategory']['allowed_values'] = [];

            $subcategories = (new Subcategory())->paginatedCollection(
                $resource_type_id,
                $parameters['category']
            );

            array_map(
                function($subcategory) use (&$conditional_parameters, $item_interface) {
                    $conditional_parameters['subcategory']['allowed_values'][$this->hash->encode('subcategory', $subcategory['subcategory_id'])] = [
                        'value' => $this->hash->encode('subcategory', $subcategory['subcategory_id']),
                        'name' => $subcategory['subcategory_name'],
                        'description' => trans('item-type-' . $item_interface->type() . '/allowed-values.description-prefix-subcategory') .
                            $subcategory['subcategory_name'] . trans('item-type-' . $item_interface->type() . '/allowed-values.description-suffix-subcategory')
                    ];
                },
                $subcategories
            );
        }

        return $conditional_parameters;
    }
}
