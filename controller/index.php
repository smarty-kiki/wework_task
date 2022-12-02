<?php

if_get('/', function ()
{
    $user_id = get_work_wechat_client_user();
    echo '<pre>';var_dump($user_id);exit;

    return render('index/index', [
        'title' => 'hello world',
        'url_infos' => [
        ],
    ]);
});

if_get('/health_check', function ()
{
    return 'ok';
});

if_get('/error_code_maps', function ()
{
    return config('error_code');
});
