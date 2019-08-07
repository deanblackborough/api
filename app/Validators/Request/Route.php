<?php
declare(strict_types=1);

namespace App\Validators\Request;

use App\Validators\Request\Routes\Category;
use App\Validators\Request\Routes\Item;
use App\Validators\Request\Routes\ItemCategory;
use App\Validators\Request\Routes\ItemSubCategory;
use App\Validators\Request\Routes\Resource;
use App\Validators\Request\Routes\ResourceType;
use App\Validators\Request\Routes\SubCategory;
use App\Utilities\Response as UtilityResponse;

/**
 * Validate the set route parameters, redirect to 404 if invalid
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2019
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class Route
{
    static public function categoryRoute($category_id)
    {
        if (Category::validate($category_id) === false) {
            UtilityResponse::notFound(trans('entities.category'));
        }
    }

    static public function subCategoryRoute($category_id, $sub_category_id)
    {
        if (SubCategory::validate($category_id, $sub_category_id) === false) {
            UtilityResponse::notFound(trans('entities.subcategory'));
        }
    }

    static public function resourceTypeRoute($resource_type_id)
    {
        if (ResourceType::validate($resource_type_id) === false) {
            UtilityResponse::notFound(trans('entities.resource-type'));
        }
    }

    static public function resourceRoute($resource_type_id, $resource_id)
    {
        if (Resource::validate($resource_type_id, $resource_id) === false) {
            UtilityResponse::notFound(trans('entities.resource'));
        }
    }

    static public function itemRoute($resource_type_id, $resource_id, $item_id)
    {
        if (Item::validate($resource_type_id, $resource_id, $item_id) === false) {
            UtilityResponse::notFound(trans('entities.item'));
        }
    }

    static public function itemCategory(
        $resource_type_id,
        $resource_id,
        $item_id,
        $item_category_id
    ) {
        if (ItemCategory::validate(
                $resource_type_id,
                $resource_id,
                $item_id,
                $item_category_id
            ) === false
        ) {
            UtilityResponse::notFound(trans('entities.item-category'));
        }
    }

    static public function itemSubCategory(
        $resource_type_id,
        $resource_id,
        $item_id,
        $item_category_id,
        $item_sub_category_id
    ) {
        if (ItemSubCategory::validate(
                $resource_type_id,
                $resource_id,
                $item_id,
                $item_category_id,
                $item_sub_category_id
            ) === false
        ) {
            UtilityResponse::notFound(trans('entities.item-subcategory'));
        }
    }
}
