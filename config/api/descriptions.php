<?php

return [
    'api' => [
        'GET_index' => 'Return all routes',
    ],
    'category' => [
        'GET_index' => 'Return the categories',
        'GET_show' => 'Return the requested category',
        'POST' => 'Create a new category',
        'PATCH' => 'Update the requested category',
        'DELETE' => 'Delete the requested category'
    ],
    'sub_category' => [
        'GET_index' => 'Return the sub categories',
        'GET_show' => 'Return the requested sub category',
        'POST' => 'Create a new sub category',
        'PATCH' => 'Update the requested sub category',
        'DELETE' => 'Delete the requested sub category'
    ],
    'resource_type' => [
        'GET_index' => 'Return the resource types',
        'GET_show' => 'Return the requested resource type',
        'POST' => 'Create a new resource type',
        'PATCH' => 'Update the requested resource type',
        'DELETE' => 'Delete the requested resource type'
    ],
    'resource' => [
        'GET_index' => 'Return the resources',
        'GET_show' => 'Return the requested resource',
        'POST' => 'Create a new resource',
        'PATCH' => 'Update the requested resource',
        'DELETE' => 'Delete the requested resource'
    ],
    'item' => [
        'GET_index' => 'Return the items',
        'GET_show' => 'Return the requested item',
        'POST' => 'Create a new item',
        'PATCH' => 'Update the requested item',
        'DELETE' => 'Delete the requested item'
    ],
    'item_category' => [
        'GET_index' => 'Return the category the item is assigned to',
        'GET_show' => 'Return the category the item is assigned to',
        'POST' => 'Assign the category',
        'PATCH' => 'Update the category',
        'DELETE' => 'Remove the assigned category'
    ],
    'item_sub_category' => [
        'GET_index' => 'Return the sub category the item is assigned to',
        'GET_show' => 'Return the sub category the item is assigned to',
        'POST' => 'Assign the sub category',
        'PATCH' => 'Update the sub category',
        'DELETE' => 'Remove the assigned sub category'
    ]
];