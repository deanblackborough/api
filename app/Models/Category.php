<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Category model
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class Category extends Model
{
    protected $table = 'category';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'category_id', 'id');
    }

    public function numberOfSubCategories()
    {
        return $this->hasMany(SubCategory::class, 'category_id', 'id')->count();
    }
}