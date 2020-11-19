<?php
declare(strict_types=1);

namespace App\Request\Validate\ItemType;

use App\ItemType\Entity;
use App\Request\Validate\Validator as BaseValidator;

/**
 * Validation helper class for items, returns the generated validator objects
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2020
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class AllocatedExpense extends BaseValidator
{
    public function __construct()
    {
        $this->entity = Entity::byType('allocated-expense');

        parent::__construct();
    }

    /**
     * Return the validator object for the create request
     *
     * @param array $options
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function create(array $options = []): \Illuminate\Contracts\Validation\Validator
    {
        return $this->createExpenseItemValidator();
    }

    /**
     * Return a valid validator object for a update (PATCH) request
     *
     * @param array $options
     *
     * @return \Illuminate\Contracts\Validation\Validator|null
     */
    public function update(array $options = []): ?\Illuminate\Contracts\Validation\Validator
    {
        return $this->updateExpenseItemValidator();
    }
}
