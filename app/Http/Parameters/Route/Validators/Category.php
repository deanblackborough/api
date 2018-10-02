<?php

namespace App\Http\Parameters\Route\Validators;

use App\Models\Category as CategoryModel;

/**
 * Validate the route params to a category
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class Category
{
    /**
     * Validate the route params are valid
     *
     * @param string|int $category_id
     *
     * @return boolean
     */
    static public function validate($category_id)
    {
        if ($category_id === 'nill' || (new CategoryModel)->find($category_id)->exists() === false) {
            return false;
        }

        return true;
    }
}
