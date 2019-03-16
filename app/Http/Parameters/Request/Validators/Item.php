<?php
declare(strict_types=1);

namespace App\Http\Parameters\Request\Validators;

use App\Http\Parameters\Request\Validators\Validator as BaseValidator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

/**
 * Validation helper class for items, returns the generated validator objects
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2019
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class Item extends BaseValidator
{
    /**
     * Return the validator object for the create request
     *
     * @param Request $request
     *
     * @return Validator
     */
    public function create(Request $request): Validator
    {
        return ValidatorFacade::make(
            $request->all(),
            Config::get('api.routes.item.validation.POST.fields'),
            Config::get('api.routes.item.validation.POST.messages')
        );
    }

    /**
     * Return the validator object for the update request
     *
     * @param Request $request
     *
     * @return Validator
     */
    public function update(Request $request): Validator
    {
        return ValidatorFacade::make(
            $request->all(),
            Config::get('api.routes.item.validation.PATCH.fields'),
            Config::get('api.routes.item.validation.PATCH.messages')
        );
    }

    public function updateFields()
    {
        return array_keys(Config::get('api.routes.item.validation.PATCH.fields'));
    }
}
