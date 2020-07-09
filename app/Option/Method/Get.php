<?php
declare(strict_types=1);

namespace App\Option\Method;

use Illuminate\Support\Facades\Config;

/**
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2020
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class Get extends Method
{
    protected bool $pagination;
    protected array $pagination_parameters;

    protected array $parameters;
    protected array $dynamic_parameters;
    protected array $parameters_after_localisation;

    protected bool $filterable;
    protected array $filterable_parameters;

    protected bool $searchable;
    protected array $searchable_parameters;

    protected bool $sortable;
    protected array $sortable_parameters;

    public function __construct()
    {
        parent::__construct();

        $this->pagination = false;
        $this->pagination_parameters = [];

        $this->parameters = [];
        $this->dynamic_parameters = [];
        $this->parameters_after_localisation = [];

        $this->filterable = false;
        $this->filterable_parameters = [];

        $this->searchable = false;
        $this->searchable_parameters = [];

        $this->sortable = false;
        $this->sortable_parameters = [];
    }

    public function setFilterableParameters(
        string $configuration_path
    ): Get
    {
        $parameters = Config::get($configuration_path);

        if (is_array($parameters) && count($parameters) > 0) {
            $this->filterable = true;
            $this->filterable_parameters = $parameters;
        }

        return $this;
    }

    public function setPaginationStatus(
        bool $status = false,
        bool $override = false
    ): Get
    {
        if ($status === true) {
            $this->pagination = true;

            if ($override === false) {
                $this->pagination_parameters = Config::get('api.app.pagination-parameters');
            } else {
                $this->pagination_parameters = Config::get('api.app.pagination-parameters-including-collection');
            }
        }

        return $this;
    }

    public function setParameters(
        string $configuration_path
    ): Get
    {
        $parameters = Config::get($configuration_path);

        if (is_array($parameters) && count($parameters) > 0) {
            $this->parameters = $parameters;
        }

        return $this;
    }

    public function setDynamicParameters(
        array $parameters = []
    ): Get
    {
        $this->dynamic_parameters = $parameters;

        return $this;
    }

    public function setSearchableParameters(
        string $configuration_path
    ): Get
    {
        $parameters = Config::get($configuration_path);

        if (is_array($parameters) && count($parameters) > 0) {
            $this->searchable = true;
            $this->searchable_parameters = $parameters;
        }

        return $this;
    }

    public function setSortableParameters(
        string $configuration_path
    ): Get
    {
        $parameters = Config::get($configuration_path);

        if (is_array($parameters) && count($parameters) > 0) {
            $this->sortable = true;
            $this->sortable_parameters = $parameters;
        }

        return $this;
    }

    protected function mergeAndLocalise(): void
    {
        foreach (
            array_merge_recursive(
                $this->pagination_parameters,
                ($this->sortable === true ? Config::get('api.app.sortable-parameters') : []),
                ($this->searchable === true ? Config::get('api.app.searchable-parameters') : []),
                ($this->filterable === true ? Config::get('api.app.filterable-parameters') : []),
                $this->parameters,
                $this->dynamic_parameters
            )
            as $parameter => $parameter_data
        ) {
            $parameter_data['title'] = trans($parameter_data['title']);
            $parameter_data['description'] = trans($parameter_data['description']);

            $this->parameters_after_localisation[$parameter] = $parameter_data;
        }
    }

    public function option(): array
    {
        $this->mergeAndLocalise();

        return [
            'description' => $this->description,
            'authentication' => [
                'required' => $this->authentication,
                'authenticated' => $this->authenticated
            ],
            'sortable' => $this->sortable_parameters,
            'searchable' => $this->searchable_parameters,
            'filterable' => $this->filterable_parameters,
            'parameters' => $this->parameters_after_localisation
        ];
    }
}
