<?php
declare(strict_types=1);

namespace App\Models\Summary\ItemType;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * @mixin QueryBuilder
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright G3D Development Limited 2018-2019
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class SimpleExpense extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $table = 'item';
    protected $sub_table = 'item_type_simple_expense';

    /**
     * Return the summary of items, grouped by category
     *
     * @param int $resource_type_id
     * @param int $resource_id
     * @param array $parameters
     *
     * @return array
     */
    public function categoriesSummary(
        int $resource_type_id,
        int $resource_id,
        array $parameters = []
    ): array
    {
        $collection = $this->
            selectRaw("
                category.id, 
                category.name AS name, 
                category.description AS description,
                SUM({$this->sub_table}.total) AS total")->
            join($this->sub_table, 'item.id', "{$this->sub_table}.item_id")->
            join("resource", "resource.id", "item.resource_id")->
            join("resource_type", "resource_type.id", "resource.resource_type_id")->
            join("item_category", "item_category.item_id", "item.id")->
            join("category", "category.id", "item_category.category_id")->
            where("category.resource_type_id", "=", $resource_type_id)->
            where("resource_type.id", "=", $resource_type_id)->
            where("resource.id", "=", $resource_id);

        return $collection->groupBy("item_category.category_id")->
            orderBy("name")->
            get()->
            toArray();
    }

    /**
     * Return the summary for a specific category
     *
     * @param int $resource_type_id
     * @param int $resource_id
     * @param int $category_id
     * @param array $parameters
     *
     * @return array
     */
    public function categorySummary(
        int $resource_type_id,
        int $resource_id,
        int $category_id,
        array $parameters = []
    ): array
    {
        $collection = $this->
            selectRaw("
                category.id, 
                category.name AS name, 
                category.description AS description, 
                SUM({$this->sub_table}.total) AS total")->
            join($this->sub_table, 'item.id', "{$this->sub_table}.item_id")->
            join("resource", "resource.id", "item.resource_id")->
            join("resource_type", "resource_type.id", "resource.resource_type_id")->
            join("item_category", "item_category.item_id", "item.id")->
            join("category", "category.id", "item_category.category_id")->
            where("category.resource_type_id", "=", $resource_type_id)->
            where("resource_type.id", "=", $resource_type_id)->
            where("resource.id", "=", $resource_id)->
            where("category.id", "=", $category_id);

        return $collection->groupBy("item_category.category_id")->
            orderBy("name")->
            get()->
            toArray();
    }

    /**
     * Return a filter summary
     *
     * @param int $resource_type_id
     * @param int $resource_id
     * @param int|null $category_id
     * @param int|null $subcategory_id
     * @param int|null $year
     * @param int|null $month
     * @param array $parameters
     * @param array $search_parameters
     *
     * @return array
     */
    public function filteredSummary(
        int $resource_type_id,
        int $resource_id,
        int $category_id = null,
        int $subcategory_id = null,
        int $year = null,
        int $month = null,
        array $parameters = [],
        array $search_parameters = []
    ): array
    {
        $collection = $this->
            selectRaw("SUM({$this->sub_table}.total) AS total")->
            join($this->sub_table, 'item.id', "{$this->sub_table}.item_id")->
            join("resource", "resource.id", "item.resource_id")->
            join("resource_type", "resource_type.id", "resource.resource_type_id")->
            join("item_category", "item_category.item_id", "item.id")->
            join("item_sub_category", "item_sub_category.item_category_id", "item_category.id")->
            join("category", "category.id", "item_category.category_id")->
            join("sub_category", "sub_category.id", "item_sub_category.sub_category_id")->
            where("resource_type.id", "=", $resource_type_id)->
            where("resource.id", "=", $resource_id);

        if ($category_id !== null) {
            $collection->where("category.id", "=", $category_id);
        }
        if ($subcategory_id !== null) {
            $collection->where("sub_category.id", "=", $subcategory_id);
        }
        if ($year !== null) {
            $collection->whereRaw(DB::raw("YEAR({$this->sub_table}.effective_date) = {$year}"));
        }
        if ($month !== null) {
            $collection->whereRaw(DB::raw("MONTH({$this->sub_table}.effective_date) = {$month}"));
        }
        if (count($search_parameters) > 0) {
            foreach ($search_parameters as $field => $search_term) {
                $collection->where("{$this->sub_table}." . $field, 'LIKE', '%' . $search_term . '%');
            }
        }

        return $collection->get()->
            toArray();
    }

    /**
     * Return a monthly summary
     *
     * @param int $resource_type_id
     * @param int $resource_id
     * @param int $year
     * @param array $parameters
     *
     * @return array
     */
    public function monthsSummary(
        int $resource_type_id,
        int $resource_id,
        int $year,
        array $parameters = []
    ): array
    {
        $collection = $this->
            selectRaw("MONTH({$this->sub_table}.effective_date) as month, SUM({$this->sub_table}.total) AS total")->
            join($this->sub_table, 'item.id', "{$this->sub_table}.item_id")->
            join("resource", "resource.id", "item.resource_id")->
            join("resource_type", "resource_type.id", "resource.resource_type_id")->
            where("resource_type.id", "=", $resource_type_id)->
            where("resource.id", "=", $resource_id)->
            whereRaw(DB::raw("YEAR({$this->sub_table}.effective_date) = '{$year}'"));

        return $collection->groupBy("month")->
            orderBy("month")->
            get()->
            toArray();
    }

    /**
     * Return a summary for a specific month
     *
     * @param int $resource_type_id
     * @param int $resource_id
     * @param int $year
     * @param int $month
     * @param array $parameters
     *
     * @return array
     */
    public function monthSummary(
        int $resource_type_id,
        int $resource_id,
        int $year,
        int $month,
        array $parameters = []
    ): array
    {
        $collection = $this->
            selectRaw("MONTH({$this->sub_table}.effective_date) as month, SUM({$this->sub_table}.total) AS total")->
            join($this->sub_table, 'item.id', "{$this->sub_table}.item_id")->
            join("resource", "resource.id", "item.resource_id")->
            join("resource_type", "resource_type.id", "resource.resource_type_id")->
            where("resource_type.id", "=", $resource_type_id)->
            where("resource.id", "=", $resource_id)->
            whereRaw(DB::raw("YEAR({$this->sub_table}.effective_date) = '{$year}'"))->
            whereRaw(DB::raw("MONTH({$this->sub_table}.effective_date) = '{$month}'"));

        return $collection->groupBy("month")->
            orderBy("month")->
            get()->
            toArray();
    }

    /**
     * Subcategories summary
     *
     * @param int $resource_type_id
     * @param int $resource_id
     * @param int $category_id
     * @param array $parameters
     *
     * @return array
     */
    public function subcategoriesSummary(
        int $resource_type_id,
        int $resource_id,
        int $category_id,
        array $parameters = []
    ): array
    {
        $collection = $this->
            selectRaw("
                sub_category.id, 
                sub_category.name AS name, 
                sub_category.description AS description,
                SUM({$this->sub_table}.total) AS total")->
            join($this->sub_table, 'item.id', "{$this->sub_table}.item_id")->
            join("resource", "resource.id", "item.resource_id")->
            join("resource_type", "resource_type.id", "resource.resource_type_id")->
            join("item_category", "item_category.item_id", "item.id")->
            join("item_sub_category", "item_sub_category.item_category_id", "item_category.id")->
            join("category", "category.id", "item_category.category_id")->
            join("sub_category", "sub_category.id", "item_sub_category.sub_category_id")->
            where("resource_type.id", "=", $resource_type_id)->
            where("resource.id", "=", $resource_id)->
            where("category.id", "=", $category_id);

        return $collection->groupBy("item_sub_category.sub_category_id")->
            orderBy("name")->
            get()->
            toArray();
    }

    /**
     * Subcategory summary
     *
     * @param int $resource_type_id
     * @param int $resource_id
     * @param int $category_id
     * @param int $subcategory_id
     * @param array $parameters
     *
     * @return array
     */
    public function subcategorySummary(
        int $resource_type_id,
        int $resource_id,
        int $category_id,
        int $subcategory_id,
        array $parameters = []
    ): array
    {
        $collection = $this->
            selectRaw("
                sub_category.id, 
                sub_category.name AS name, 
                sub_category.description AS description,
                SUM({$this->sub_table}.total) AS total")->
            join($this->sub_table, 'item.id', "{$this->sub_table}.item_id")->
            join("resource", "resource.id", "item.resource_id")->
            join("resource_type", "resource_type.id", "resource.resource_type_id")->
            join("item_category", "item_category.item_id", "item.id")->
            join("item_sub_category", "item_sub_category.item_category_id", "item_category.id")->
            join("category", "category.id", "item_category.category_id")->
            join("sub_category", "sub_category.id", "item_sub_category.sub_category_id")->
            where("resource_type.id", "=", $resource_type_id)->
            where("resource.id", "=", $resource_id)->
            where("category.id", "=", $category_id)->
            where("sub_category.id", "=", $subcategory_id);

        return $collection->groupBy("item_sub_category.sub_category_id")->
            orderBy("name")->
            get()->
            toArray();
    }

    /**
     * Return the total summary for all items
     *
     * @param int $resource_type_id
     * @param int $resource_id
     * @param array $parameters
     *
     * @return array
     */
    public function summary(
        int $resource_type_id,
        int $resource_id,
        array $parameters = []
    ): array
    {
        $collection = $this->selectRaw("sum({$this->sub_table}.total) AS total")->
            join($this->sub_table, 'item.id', "{$this->sub_table}.item_id")->
            join('resource', 'item.resource_id', 'resource.id')->
            where('resource_id', '=', $resource_id)->
            where('resource.resource_type_id', '=', $resource_type_id);

        return $collection->get()
            ->toArray();
    }

    /**
     * Return the summary grouped by years
     *
     * @param int $resource_type_id
     * @param int $resource_id
     * @param array $parameters
     *
     * @return array
     */
    public function yearsSummary(
        int $resource_type_id,
        int $resource_id,
        array $parameters = []
    ): array
    {
        $collection = $this->
            selectRaw("YEAR({$this->sub_table}.effective_date) as year, SUM({$this->sub_table}.total) AS total")->
            join($this->sub_table, 'item.id', "{$this->sub_table}.item_id")->
            join("resource", "resource.id", "item.resource_id")->
            join("resource_type", "resource_type.id", "resource.resource_type_id")->
            where("resource_type.id", "=", $resource_type_id)->
            where("resource.id", "=", $resource_id);

        return $collection->groupBy("year")->
            orderBy("year")->
            get()->
            toArray();
    }

    /**
     * Return a summary for a specific year
     *
     * @param int $resource_type_id
     * @param int $resource_id
     * @param int $year
     * @param array $parameters
     *
     * @return array
     */
    public function yearSummary(
        int $resource_type_id,
        int $resource_id,
        int $year,
        array $parameters = []
    ): array
    {
        $collection = $this->
            selectRaw("YEAR({$this->sub_table}.effective_date) as year, SUM({$this->sub_table}.total) AS total")->
            join($this->sub_table, 'item.id', "{$this->sub_table}.item_id")->
            join("resource", "resource.id", "item.resource_id")->
            join("resource_type", "resource_type.id", "resource.resource_type_id")->
            where("resource_type.id", "=", $resource_type_id)->
            where("resource.id", "=", $resource_id)->
            whereRaw(DB::raw("YEAR({$this->sub_table}.effective_date) = '{$year}'"));

        return $collection->groupBy("year")->
            get()->
            toArray();
    }
}