<?php

if_get('/', function ()
{
    $user_id = get_work_wechat_client_user();
    log_module('debug', $user_id);
    $config = config('work_wechat');
    $signature_info = work_wechat_get_js_sdk_signature_info(uri());
    log_module('debug', print_r($signature_info, true));

    return render('index/index', [
        'user_id' => $user_id,
        'config'  => $config,
        'signature_info' => $signature_info,
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
