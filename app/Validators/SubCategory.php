<?php

namespace App\Validators;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

/**
 * Validation helper class for sub categories, returns the generated validator objects
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class SubCategory
{
    /**
     * Create the validation rules for the create request
     *
     * @param integer $category_id
     *
     * @return array
     */
    static private function createRules(int $category_id): array
    {
        return array_merge(
            [
                'name' => [
                    'required',
                    'string',
                    'unique:sub_category,name,null,id,category_id,' . $category_id
                ],
            ],
            Config::get('routes.sub_category.validation.POST.fields')
        );
    }
    
    /**
     * Return the validator object for the create request
     *
     * @param Request $request
     * @param integer category_id
     *
     * @return Validator
     */
    static public function create(Request $request, int $category_id): Validator
    {
        return ValidatorFacade::make(
            $request->all(),
            self::createRules($category_id),
            Config::get('routes.sub_category.validation.POST.messages')
        );
    }

    /**
     * Return the validator object for the update request
     *
     * @param Request $request
     * @param integer $category_id
     * @param integer $sub_category_id
     *
     * @return Validator
     */
    static public function update(Request $request, int $category_id, int $sub_category_id): Validator
    {
        return ValidatorFacade::make(
            $request->all(),
            Config::get('routes.sub_category.validation.PATCH.fields'),
            Config::get('routes.sub_category.validation.POST.messages')
        );
    }
}