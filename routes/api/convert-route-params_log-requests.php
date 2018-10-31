<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => Config::get('api.version.prefix'),
        'middleware' => [
            'convert.route.parameters',
            'log.requests'
        ]
    ],
    function () {
        Route::get(
            'categories/{category_id}',
            'CategoryController@show'
        );
        Route::options(
            'categories/{category_id}',
            'CategoryController@optionsShow'
        );

        Route::get(
            'categories/{category_id}/sub_categories',
            'SubCategoryController@index'
        );
        Route::options(
            'categories/{category_id}/sub_categories',
            'SubCategoryController@optionsIndex'
        );
        Route::get(
            'categories/{category_id}/sub_categories/{sub_category_id}',
            'SubCategoryController@show'
        );
        Route::options(
            'categories/{category_id}/sub_categories/{sub_category_id}',
            'SubCategoryController@optionsShow'
        );

        Route::get(
            'resource_types',
            'ResourceTypeController@index'
        );
        Route::options(
            'resource_types',
            'ResourceTypeController@optionsIndex'
        );
        Route::get(
            'resource_types/{resource_type_id}',
            'ResourceTypeController@show'
        );
        Route::options(
            'resource_types/{resource_type_id}',
            'ResourceTypeController@optionsShow'
        );

        Route::get(
            'resource_types/{resource_type_id}/resources',
            'ResourceController@index'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources',
            'ResourceController@optionsIndex'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}',
            'ResourceController@show'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}',
            'ResourceController@optionsShow'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/items/{item_id}',
            'ItemController@show'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/items/{item_id}',
            'ItemController@optionsShow'
        );

        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/items/{item_id}/category',
            'ItemCategoryController@index'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/items/{item_id}/category',
            'ItemCategoryController@optionsIndex'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/items/{item_id}/category/{item_category_id}',
            'ItemCategoryController@show'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/items/{item_id}/category/{item_category_id}',
            'ItemCategoryController@optionsShow'
        );

        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/items/{item_id}/category/{item_category_id}/sub_category',
            'ItemSubCategoryController@index'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/items/{item_id}/category/{item_category_id}/sub_category',
            'ItemSubCategoryController@optionsIndex'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/items/{item_id}/category/{item_category_id}/sub_category/{item_sub_category_id}',
            'ItemSubCategoryController@show'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/items/{item_id}/category/{item_category_id}/sub_category/{item_sub_category_id}',
            'ItemSubCategoryController@optionsShow'
        );

        // Summary end points
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/categories',
            'SummaryController@categories'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/categories',
            'SummaryController@optionsCategories'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/categories/{category_id}',
            'SummaryController@category'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/categories/{category_id}',
            'SummaryController@optionsCategory'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/categories/{category_id}/sub_categories',
            'SummaryController@subCategories'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/categories/{category_id}/sub_categories',
            'SummaryController@optionsSubCategories'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/categories/{category_id}/sub_categories/{sub_category_id}',
            'SummaryController@subCategory'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/categories/{category_id}/sub_categories/{sub_category_id}',
            'SummaryController@optionsSubCategory'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/tco',
            'SummaryController@tco'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/tco',
            'SummaryController@optionsTco'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/years',
            'SummaryController@years'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/years',
            'SummaryController@optionsYears'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/years/{year}',
            'SummaryController@year'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/years/{year}',
            'SummaryController@optionsYear'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/years/{year}/months',
            'SummaryController@months'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/years/{year}/months',
            'SummaryController@optionsMonths'
        );
        Route::get(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/years/{year}/months/{month}',
            'SummaryController@month'
        );
        Route::options(
            'resource_types/{resource_type_id}/resources/{resource_id}/summary/years/{year}/months/{month}',
            'SummaryController@optionsMonth'
        );
    }
);
