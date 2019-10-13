<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => Config::get('api.app.version.prefix'),
    ],
    function () {
        Route::post(
            'auth/login',
            'PassportController@login'
        );

        Route::post(
            'auth/user',
            'PassportController@user'
        );

        Route::post(
            'auth/register',
            'PassportController@register'
        );
    }
);
