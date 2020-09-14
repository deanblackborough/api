<?php

namespace App\Http\Controllers;

use App\Jobs\ClearCache;
use App\Request\BodyValidation;
Use App\Response\Cache;
use App\Request\Route;
use App\Models\Category;
use App\Models\Transformers\Category as CategoryTransformer;
use App\Request\Validate\Category as CategoryValidator;
use App\Response\Responses;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

/**
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2020
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class CategoryManage extends Controller
{
    /**
     * Create a new category
     *
     * @param $resource_type_id
     *
     * @return JsonResponse
     */
    public function create($resource_type_id): JsonResponse
    {
        Route\Validate::resourceType(
            (int) $resource_type_id,
            $this->permitted_resource_types
        );

        $validator = (new CategoryValidator)->create([
            'resource_type_id' => $resource_type_id
        ]);
        BodyValidation::validateAndReturnErrors($validator);

        $cache_job_payload = (new Cache\JobPayload())
            ->setGroupKey(Cache\KeyGroup::CATEGORY_CREATE)
            ->setRouteParameters([
                'resource_type_id' => $resource_type_id
            ])
            ->setPermittedUser(in_array($resource_type_id, $this->permitted_resource_types, true))
            ->setUserId($this->user_id);

        try {
            $category = new Category([
                'name' => request()->input('name'),
                'description' => request()->input('description'),
                'resource_type_id' => $resource_type_id
            ]);
            $category->save();

            ClearCache::dispatch($cache_job_payload->payload());

        } catch (Exception $e) {
           Responses::failedToSaveModelForCreate();
        }

        return response()->json(
            (new CategoryTransformer((new Category)->instanceToArray($category)))->asArray(),
            201
        );
    }

    /**
     * Delete the requested category
     *
     * @param $resource_type_id
     * @param $category_id
     *
     * @return JsonResponse
     */
    public function delete(
        $resource_type_id,
        $category_id
    ): JsonResponse
    {
        Route\Validate::category(
            (int) $resource_type_id,
            (int) $category_id,
            $this->permitted_resource_types,
            true
        );

        $cache_job_payload = (new Cache\JobPayload())
            ->setGroupKey(Cache\KeyGroup::CATEGORY_DELETE)
            ->setRouteParameters([
                'resource_type_id' => $resource_type_id
            ])
            ->setPermittedUser(in_array($resource_type_id, $this->permitted_resource_types, true))
            ->setUserId($this->user_id);

        $category = (new Category())->find($category_id);
        if ($category === null) {
            Responses::notFound(trans('entities.category'));
        }

        try {
            $category->delete();

            ClearCache::dispatch($cache_job_payload->payload());

            Responses::successNoContent();
        } catch (QueryException $e) {
            Responses::foreignKeyConstraintError();
        } catch (Exception $e) {
            Responses::notFound(trans('entities.category'));
        }
    }

    /**
     * Update the selected category
     *
     * @param $resource_type_id
     * @param $category_id
     *
     * @return JsonResponse
     */
    public function update($resource_type_id, $category_id): JsonResponse
    {
        Route\Validate::category(
            (int) $resource_type_id,
            (int) $category_id,
            $this->permitted_resource_types,
            true
        );

        $category = (new Category())->instance($category_id);

        if ($category === null) {
            Responses::failedToSelectModelForUpdateOrDelete();
        }

        BodyValidation::checkForEmptyPatch();

        $validator = (new CategoryValidator)->update([
            'resource_type_id' => (int) $category->resource_type_id,
            'category_id' => (int) $category_id
        ]);

        if ($validator === null) {
            Responses::failedToSelectModelForUpdateOrDelete();
        }

        BodyValidation::validateAndReturnErrors($validator);

        BodyValidation::checkForInvalidFields(
            array_merge(
                (new Category())->patchableFields(),
                (new CategoryValidator)->dynamicDefinedFields()
            )
        );

        foreach (request()->all() as $key => $value) {
            $category->$key = $value;
        }

        $cache_job_payload = (new Cache\JobPayload())
            ->setGroupKey(Cache\KeyGroup::CATEGORY_UPDATE)
            ->setRouteParameters([
                'resource_type_id' => $resource_type_id
            ])
            ->setPermittedUser(in_array($resource_type_id, $this->permitted_resource_types, true))
            ->setUserId($this->user_id);

        try {
            $category->save();

            ClearCache::dispatch($cache_job_payload->payload());

        } catch (Exception $e) {
            Responses::failedToSaveModelForUpdate();
        }

        Responses::successNoContent();
    }
}
