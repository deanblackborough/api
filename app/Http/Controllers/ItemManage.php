<?php

namespace App\Http\Controllers;

use App\ItemType\Entity;
use App\Jobs\ClearCache;
use App\Models\Item;
use App\Models\ItemTransfer;
use App\Response\Responses;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Manage items
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2021
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class ItemManage extends Controller
{
    public function create(
        string $resource_type_id,
        string $resource_id
    ): JsonResponse
    {
        if ($this->writeAccessToResourceType((int) $resource_type_id) === false) {
            \App\Response\Responses::notFoundOrNotAccessible(trans('entities.resource'));
        }

        $user_id = $this->user_id;

        $entity = Entity::item($resource_type_id);

        $validation = $entity->validator();
        $validator = $validation->create();

        if ($validator->fails()) {
            return \App\Request\BodyValidation::returnValidationErrors($validator);
        }

        $model = $entity->model();

        $cache_job_payload = (new \App\Cache\JobPayload())
            ->setGroupKey(\App\Cache\KeyGroup::ITEM_CREATE)
            ->setRouteParameters([
                'resource_type_id' => $resource_type_id,
                'resource_id' => $resource_id
            ])
            ->setPermittedUser($this->writeAccessToResourceType((int) $resource_type_id))
            ->setUserId($user_id);

        try {
            [$item, $item_type] = DB::transaction(static function() use ($resource_id, $user_id, $entity) {
                $item = new Item([
                    'resource_id' => $resource_id,
                    'created_by' => $user_id
                ]);
                $item->save();

                $item_type = $entity->create((int) $item->id);

                return [$item, $item_type];
            });

            ClearCache::dispatch($cache_job_payload->payload());

        } catch (Exception $e) {
            return \App\Response\Responses::failedToSaveModelForCreate();
        }

        return response()->json(
            $entity->transformer($model->instanceToArray($item, $item_type))->asArray(),
            201
        );
    }

    public function update(
        string $resource_type_id,
        string $resource_id,
        string $item_id
    ): JsonResponse
    {
        if ($this->writeAccessToResourceType((int) $resource_type_id) === false) {
            \App\Response\Responses::notFoundOrNotAccessible(trans('entities.item'));
        }

        $entity = Entity::item($resource_type_id);

        if (count(request()->all()) === 0) {
            return \App\Response\Responses::nothingToPatch();
        }

        $invalid_fields = \App\Request\BodyValidation::checkForInvalidFields(array_keys($entity->patchValidation()));

        if (count($invalid_fields) > 0) {
            return Responses::invalidFieldsInRequest($invalid_fields);
        }

        $validation = $entity->validator();
        $validator = $validation->update();

        if ($validator->fails()) {
            return \App\Request\BodyValidation::returnValidationErrors($validator);
        }

        $cache_job_payload = (new \App\Cache\JobPayload())
            ->setGroupKey(\App\Cache\KeyGroup::ITEM_DELETE)
            ->setRouteParameters([
                'resource_type_id' => $resource_type_id,
                'resource_id' => $resource_id
            ])
            ->setPermittedUser($this->writeAccessToResourceType((int) $resource_type_id))
            ->setUserId($this->user_id);

        $item = (new Item())->instance($resource_type_id, $resource_id, $item_id);
        $item_type = $entity->instance((int) $item_id);

        if ($item === null || $item_type === null) {
            return \App\Response\Responses::failedToSelectModelForUpdateOrDelete();
        }

        try {
            $item->updated_by = $this->user_id;

            DB::transaction(static function() use ($item, $entity, $item_type) {
                if ($item->save() === true) {
                    $entity->update(request()->all(), $item_type);
                }
            });

            ClearCache::dispatch($cache_job_payload->payload());

        } catch (Exception $e) {
            return \App\Response\Responses::failedToSaveModelForUpdate();
        }

        return \App\Response\Responses::successNoContent();
    }

    public function delete(
        string $resource_type_id,
        string $resource_id,
        string $item_id
    ): JsonResponse
    {
        if ($this->writeAccessToResourceType((int) $resource_type_id) === false) {
            \App\Response\Responses::notFoundOrNotAccessible(trans('entities.item'));
        }

        $entity = Entity::item($resource_type_id);

        $cache_job_payload = (new \App\Cache\JobPayload())
            ->setGroupKey(\App\Cache\KeyGroup::ITEM_DELETE)
            ->setRouteParameters([
                'resource_type_id' => $resource_type_id,
                'resource_id' => $resource_id
            ])
            ->setPermittedUser($this->writeAccessToResourceType((int) $resource_type_id))
            ->setUserId($this->user_id);

        $item_model = $entity->model();

        $item_type = $item_model->instance($item_id);
        $item = (new Item())->instance($resource_type_id, $resource_id, $item_id);

        if ($item === null || $item_type === null) {
            return \App\Response\Responses::notFound(trans('entities.item'));
        }

        if (in_array($entity->type(), ['allocated-expense', 'simple-expense']) &&
            $item_model->hasCategoryAssignments($item_id) === true) {
                return \App\Response\Responses::foreignKeyConstraintError();
        }

        try {
            DB::transaction(static function() use ($item_id, $item_type, $item) {
                (new ItemTransfer())->deleteTransfers($item_id);
                $item_type->delete();
                $item->delete();
            });

            ClearCache::dispatchNow($cache_job_payload->payload());

            return \App\Response\Responses::successNoContent();
        } catch (QueryException $e) {
            return \App\Response\Responses::foreignKeyConstraintError();
        } catch (Exception $e) {
            return \App\Response\Responses::notFound(trans('entities.item'));
        }
    }
}
