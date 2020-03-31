<?php

declare(strict_types=1);

return [
    'api_GET_index' => 'Return all the API routes',
    'api_GET_changelog' => 'Return the complete API changelog',

    'category_GET_index' => 'Return all the public categories, optionally, with authorisation include any private categories',
    'category_GET_show' => 'Return the selected category',
    'category_POST' => 'Create a new category',
    'category_PATCH' => 'Update the selected category',
    'category_DELETE' => 'Delete the selected category',

    'sub_category_GET_index' => 'Return all the subcategories that are children of the selected category',
    'sub_category_GET_show' => 'Return the selected subcategory',
    'sub_category_POST' => 'Create a new subcategory',
    'sub_category_PATCH' => 'Update the selected subcategory',
    'sub_category_DELETE' => 'Delete the selected subcategory',

    'item_type_GET_index' => 'Return all the item types supported in the API',
    'item_type_GET_show' => 'Return the selected item type',

    'resource_type_GET_index' => 'Return all the public resource types, optionally, with authorisation include any private resource types',
    'resource_type_GET_show' => 'Return the selected resource type',
    'resource_type_POST' => 'Create a new resource type',
    'resource_type_PATCH' => 'Update the selected resource type',
    'resource_type_DELETE' => 'Delete the selected resource type',

    'resource_type_item_GET_index' => 'Return all the items assigned to the resources for this resource type',

    'resource_GET_index' => 'Return all the resources that are children of the selected resource type',
    'resource_GET_show' => 'Return the selected resource',
    'resource_POST' => 'Create a new resource',
    'resource_PATCH' => 'Update the selected resource',
    'resource_DELETE' => 'Delete the selected resource',

    'item_GET_index' => 'Return all the items that are children of the selected resource',
    'item_GET_show' => 'Return the selected item',
    'item_POST' => 'Create a new item',
    'item_PATCH' => 'Update the selected item',
    'item_DELETE' => 'Delete the selected item',

    'item_category_GET_index' => 'Return the category assigned to the selected item',
    'item_category_GET_show' => 'Return the category assigned to the selected item',
    'item_category_POST' => 'Assign a category to the selected item',
    'item_category_PATCH' => 'Update the category assigned to the selected item',
    'item_category_DELETE' => 'Delete the category assigned to the selected item',

    'item_sub_category_GET_index' => 'Return the subcategory assigned to the selected item',
    'item_sub_category_GET_show' => 'Return the subcategory assigned to the selected item',
    'item_sub_category_POST' => 'Assign a subcategory to the selected item',
    'item_sub_category_PATCH' => 'Update the subcategory assigned to the selected item',
    'item_sub_category_DELETE' => 'Delete the subcategory assigned to the selected item',

    'item_transfer_GET_index' => 'Return the transfers for the selected resource type',
    'item_transfer_GET_show' => 'Return the selected transfer',
    'item_transfer_POST' => 'Transfer an item to another resource',

    'item_partial_transfer_GET_index' => 'Return the partial transfers for the selected resource type',
    'item_partial_transfer_GET_show' => 'Return the selected partial transfer',
    'item_partial_transfer_POST' => 'Portion a percentage of the total for an item to another resource',

    'permitted_user_GET_index' => 'Return the permitted users',
    'permitted_user_POST' => 'Assign a permitted user',

    'request_GET_access-log' => 'Return the access log, read requests',
    'request_GET_error_log' => 'Return the error log',
    'request_POST' => 'Create an error log report',

    'summary_category_GET_index' => 'Return a summary of the categories',
    'summary_subcategory_GET_index' => 'Return a summary of the subcategories',

    'summary_GET_request_access-log' => 'Return a summary of the access log, all read requests, grouped by year and month',

    'summary-resource-type-GET-index' => 'Return a summary of the resource types',
    'summary-resource-GET-index' => 'Return a summary of the resources',

    'summary_GET_resource-type_resource_items' => 'Return the TCO (Total cost of ownership, sum of items) for the selected resource',

    'summary-resource-type-item-GET-index' => 'Return a summary of the items for all the resources matching this resource type',
];
