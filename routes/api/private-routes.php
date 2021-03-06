<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => Config::get('api.app.version.prefix'),
        'middleware' => [
            'auth:sanctum',
            'convert.route.parameters'
        ]
    ],
    static function () {
        Route::get(
            'auth/user',
            'Authentication@user'
        );

        Route::post(
            'resource-types',
            'ResourceTypeManage@create'
        )->name('resource-type.create');

        Route::post(
            'resource-types/{resource_type_id}/categories',
            'CategoryManage@create'
        );

        Route::post(
            'resource-types/{resource_type_id}/categories/{category_id}/subcategories',
            'SubcategoryManage@create'
        );

        Route::post(
            'resource-types/{resource_type_id}/resources',
            'ResourceManage@create'
        )->name('resource.create');

        Route::post(
            'resource-types/{resource_type_id}/resources/{resource_id}/items',
            'ItemManage@create'
        );

        Route::post(
            'resource-types/{resource_type_id}/resources/{resource_id}/items/{item_id}/categories',
            'ItemCategoryManage@create'
        );

        Route::post(
            'resource-types/{resource_type_id}/resources/{resource_id}/items/{item_id}/categories/{item_category_id}/subcategories',
            'ItemSubcategoryManage@create'
        );

        Route::post(
            'resource-types/{resource_type_id}/resources/{resource_id}/items/{item_id}/partial-transfer',
            'ItemPartialTransferManage@transfer'
        );

        Route::post(
            'resource-types/{resource_type_id}/resources/{resource_id}/items/{item_id}/transfer',
            'ItemTransferManage@transfer'
        );

        Route::delete(
            'resource-types/{resource_type_id}',
            'ResourceTypeManage@delete'
        )->name('resource-type.delete');

        Route::delete(
            'resource-types/{resource_type_id}/categories/{category_id}',
            'CategoryManage@delete'
        );

        Route::delete(
            'resource-types/{resource_type_id}/categories/{category_id}/subcategories/{subcategory_id}',
            'SubcategoryManage@delete'
        );

        Route::delete(
            'resource-types/{resource_type_id}/partial-transfers/{item_partial_transfer_id}',
            'ItemPartialTransferManage@delete'
        );

        Route::delete(
            'resource-types/{resource_type_id}/resources/{resource_id}',
            'ResourceManage@delete'
        );

        Route::delete(
            'resource-types/{resource_type_id}/resources/{resource_id}/items/{item_id}',
            'ItemManage@delete'
        );

        Route::delete(
            'resource-types/{resource_type_id}/resources/{resource_id}/items/{item_id}/categories/{item_category_id}',
            'ItemCategoryManage@delete'
        );

        Route::delete(
            'resource-types/{resource_type_id}/resources/{resource_id}/items/{item_id}/categories/{item_category_id}/subcategories/{item_subcategory_id}',
            'ItemSubcategoryManage@delete'
        );

        Route::patch(
            'resource-types/{resource_type_id}',
            'ResourceTypeManage@update'
        )->name('resource-type.update');

        Route::patch(
            'resource-types/{resource_type_id}/categories/{category_id}',
            'CategoryManage@update'
        );

        Route::patch(
            'resource-types/{resource_type_id}/categories/{category_id}/subcategories/{subcategory_id}',
            'SubcategoryManage@update'
        );

        Route::patch(
            'resource-types/{resource_type_id}/resources/{resource_id}',
            'ResourceManage@update'
        );

        Route::patch(
            'resource-types/{resource_type_id}/resources/{resource_id}/items/{item_id}',
            'ItemManage@update'
        );

        Route::get(
            'tools/cache',
            'ToolManage@cache'
        );

        Route::delete(
            'tools/cache',
            'ToolManage@deleteCache'
        );
    }
);
