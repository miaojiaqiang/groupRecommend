<?php

$api = app('Dingo\Api\Routing\Router');

/*
|--------------------------------------------------------------------------
| Manage API Routes
|--------------------------------------------------------------------------
*/


$api->version('v1', ['prefix' => '/api/manage', 'namespace' => 'App\Api\Controllers\Manage'], function ($api) {
    #
    $api->group(['namespace' => 'User'], function ($api) {
        $api->post('login', 'CommonController@login');
    });
    $api->group(['namespace' => 'Home'], function ($api) {
        $api->post('upload', 'CommonController@upload');
    });

    # Login
    $api->group(['middleware' => 'jwt.auth'], function ($api) {
        # User
        $api->group(['namespace' => 'User'], function ($api) {
            $api->get('token_refresh','CommonController@tokenRefresh');
            # /manage/user
            $api->group(['prefix' => 'user'], function ($api) {
                #微信用户
                $api->get('member/search','MemberController@search');
                $api->resource('member', 'MemberController');
                #拉黑
                $api->patch('member/{id}/block', 'MemberController@upBlock');
                # User Info
                $api->get('info', 'CommonController@info');

                #群管理
                $api->post('group/test','GroupController@test');
                $api->get('group/search','GroupController@search');
                $api->resource('group','GroupController');
                #商家
                $api->get('business/lists','BusinessController@lists');
                $api->get('business/search','BusinessController@search');
                $api->resource('business','BusinessController');

            });
        });

        # Home
        $api->group(['namespace' => 'Home'], function ($api) {
            $api->get('menus', 'CommonController@menus');
            $api->get('map_key', 'CommonController@mapKey');

            # /manage/home
            $api->group(['prefix' => 'home'], function ($api) {
                $api->get('menu', 'MenuController@lists');

                $api->resource('banner', 'BannerController');
                #改变状态
                $api->patch('banner/{id}/show', 'BannerController@upShow');

            });


        });







    });

});
