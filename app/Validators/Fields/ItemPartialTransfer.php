<?php
declare(strict_types=1);

namespace App\Validators\Fields;

use App\Validators\Fields\Validator as BaseValidator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;

/**
 * Validation helper class for item transfer, returns the generated validator
 * objects
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2020
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class ItemPartialTransfer extends BaseValidator
{
    /**
     * Return the validator object for the create request
     *
     * @param array $options
     *
     * @return Validator
     */
    public function create(array $options = []): Validator
    {
        $this->requiredIndexes([
                'resource_type_id',
                'existing_resource_id'
            ],
            $options
        );

        $decode = $this->hash->resource()->decode(request()->input('resource_id'));
        $resource_id = null;
        if (count($decode) === 1) {
            $resource_id = $decode[0];
        }

        // We need to merge the decoded resource_id with the POSTed data
        return ValidatorFacade::make(
            array_merge(
                request()->all(),
                [
                    'resource_id' => $resource_id,
                ]
            ),
            array_merge(
                [
                    'resource_id' => [
                        'required',
                        Rule::exists('resource', 'id')->where(function ($query) use ($options)
                        {
                            $query->where('resource_type_id', '=', $options['resource_type_id'])->
                                where('id', '!=', $options['existing_resource_id']);
                        }),
                    ],
                ],
                Config::get('api.item-partial-transfer.validation.POST.fields')
            ),
            $this->translateMessages('api.item-partial-transfer.validation.POST.messages')
        );
    }

    /**
     * @param array $options
     *
     * @return Validator
     */
    public function update(array $options = []): \Illuminate\Contracts\Validation\Validator
    {
        // TODO: Implement update() method.
    }
}