<?php

namespace App\Http\Controllers;

use App\Entity\Item\Entity;
use App\Models\ItemTransfer;
use App\Response\Cache;
use App\Request\Route;
use App\Models\Item;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Manage items
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2020
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class ItemManage extends Controller
{
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
        Route\Validate::resource(
            $resource_type_id,
            $resource_id,
            $this->permitted_resource_types,
            true
        );

        $user_id = $this->user_id;

        $cache_control = new Cache\Control(
            $this->user_id,
            in_array($resource_type_id, $this->permitted_resource_types, true)
        );
        $cache_key = new Cache\Key();

        $entity = Entity::item($resource_type_id);

        $validation = $entity->validator();
        $validator = $validation->create();
        \App\Request\BodyValidation::validateAndReturnErrors($validator);

        $model = $entity->model();

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

            $cache_trash = new Cache\Trash(
                $cache_control,
                [
                    $cache_key->resourceTypeItems($resource_type_id),
                    $cache_key->items($resource_type_id, $resource_id)
                ],
                $resource_type_id,
                $this->public_resource_types,
                $this->permittedUsers($resource_type_id)
            );
            $cache_trash->all();

        } catch (Exception $e) {
            \App\Response\Responses::failedToSaveModelForCreate();
        }

        return response()->json(
            $entity->transformer($model->instanceToArray($item, $item_type))->asArray(),
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
        Route\Validate::item(
            $resource_type_id,
            $resource_id,
            $item_id,
            $this->permitted_resource_types,
            true
        );

        $cache_control = new Cache\Control(
            $this->user_id,
            in_array($resource_type_id, $this->permitted_resource_types, true)
        );
        $cache_key = new Cache\Key();

        $entity = Entity::item($resource_type_id);

        \App\Request\BodyValidation::checkForEmptyPatch();

        \App\Request\BodyValidation::checkForInvalidFields(array_keys($entity->patchValidation()));

        $validation = $entity->validator();
        $validator = $validation->update();
        \App\Request\BodyValidation::validateAndReturnErrors($validator);

        $item = (new Item())->instance($resource_type_id, $resource_id, $item_id);
        $item_type = $entity->instance((int) $item_id);

        if ($item === null || $item_type === null) {
            \App\Response\Responses::failedToSelectModelForUpdateOrDelete();
        }

        try {
            $item->updated_by = $this->user_id;

            DB::transaction(static function() use ($item, $entity, $item_type) {
                if ($item->save() === true) {
                    $entity->update(request()->all(), $item_type);
                }
            });

            $cache_trash = new Cache\Trash(
                $cache_control,
                [
                    $cache_key->resourceTypeItems($resource_type_id),
                    $cache_key->items($resource_type_id, $resource_id)
                ],
                $resource_type_id,
                $this->public_resource_types,
                $this->permittedUsers($resource_type_id)
            );
            $cache_trash->all();

        } catch (Exception $e) {
            \App\Response\Responses::failedToSaveModelForUpdate();
        }

        return \App\Response\Responses::successNoContent();
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
        Route\Validate::resource(
            $resource_type_id,
            $resource_id,
            $this->permitted_resource_types,
            true
        );

        $cache_control = new Cache\Control(
            $this->user_id,
            in_array($resource_type_id, $this->permitted_resource_types, true)
        );
        $cache_key = new Cache\Key();

        $entity = Entity::item($resource_type_id);

        $item_model = $entity->model();

        $item_type = $item_model->instance($item_id);
        $item = (new Item())->instance($resource_type_id, $resource_id, $item_id);

        if ($item === null || $item_type === null) {
            \App\Response\Responses::notFound(trans('entities.item'));
        }

        if (in_array($entity->type(), ['allocated-expense', 'simple-expense']) &&
            $item_model->hasCategoryAssignments($item_id) === true) {
                \App\Response\Responses::foreignKeyConstraintError();
        }

        try {
            DB::transaction(static function() use ($item_id, $item_type, $item) {
                (new ItemTransfer())->deleteTransfers($item_id);
                $item_type->delete();
                $item->delete();
            });

            $cache_trash = new Cache\Trash(
                $cache_control,
                [
                    $cache_key->resourceTypeItems($resource_type_id),
                    $cache_key->items($resource_type_id, $resource_id)
                ],
                $resource_type_id,
                $this->public_resource_types,
                $this->permittedUsers($resource_type_id)
            );
            $cache_trash->all();

            \App\Response\Responses::successNoContent();
        } catch (QueryException $e) {
            \App\Response\Responses::foreignKeyConstraintError();
        } catch (Exception $e) {
            \App\Response\Responses::notFound(trans('entities.item'));
        }
    }
}