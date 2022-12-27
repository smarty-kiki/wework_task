<?php

const WORK_WECHAT_API_PREFIX = 'https://qyapi.weixin.qq.com/cgi-bin';
const WORK_WECHAT_ERRCODE_SUCCESS = 0;
const WORK_WECHAT_ERRCODE_ACCESS_TOKEN_EXPIRED = 42001;

const WORK_WECHAT_LOG_MODULE = 'work_wechat';

function work_wechat_if_res_success($res)
{/*{{{*/
    return $res['errcode'] == WORK_WECHAT_ERRCODE_SUCCESS;
}/*}}}*/

function _work_wechat_get_access_token()
{/*{{{*/
    $config = config('work_wechat');

    return http_json(WORK_WECHAT_API_PREFIX.'/gettoken?'.http_build_query([
        'corpid'     => $config['corpid'],
        'corpsecret' => $config['corpsecret'],
    ]));
}/*}}}*/

function _work_wechat_closure(closure $action)
{/*{{{*/
    static $access_token = null;

    if (is_null($access_token)) {
        $access_token = cache_get('work_wechat_access_token');
    }

    $expire_retry = 3;
    while ($expire_retry > 0) {
        $expire_retry --;

        if (empty($access_token)) {
            $res = _work_wechat_get_access_token();
            if (work_wechat_if_res_success($res)) {
                $access_token = $res['access_token'];
                cache_set('work_wechat_access_token', $access_token);
            } else {
                return $res;
            }
        }

        $res = call_user_func($action, $access_token);
        if ($res['errcode'] == WORK_WECHAT_ERRCODE_ACCESS_TOKEN_EXPIRED) {
            $access_token = null;
        } else {
            return $res;
        }
    }

    return $res;
}/*}}}*/

function work_wechat_get_user_info($code)
{/*{{{*/
    return _work_wechat_closure(function ($access_token) use ($code) {
        log_module('work_wechat', '使用 access_token 获取用户信息:'.$access_token);
        log_module('work_wechat', '使用 code 获取用户信息:'.$code);
        return http_json(WORK_WECHAT_API_PREFIX.'/auth/getuserinfo?'.http_build_query([
            'access_token' => $access_token,
            'code'         => $code,
        ]));
    });
}/*}}}*/

function work_wechat_build_oauth_redirect_url($url)
{/*{{{*/
    $work_wechat_config = config('work_wechat');

    return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$work_wechat_config['corpid'].'&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_base&state=1#wechat_redirect';
}/*}}}*/

function work_wechat_get_js_sdk_signature_info($url)
{/*{{{*/
    $jsapi_ticket = work_wechat_get_jsapi_ticket();
    $nonceStr = time();
    $timestamp = time();
    $signature = sha1(sprintf('jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s', $jsapi_ticket, $nonceStr, $timestamp, $url));

    return [
        'jsapi_ticket' => $jsapi_ticket,
        'nonce_str'    => $nonceStr,
        'timestamp'    => $timestamp,
        'signature'    => $signature,
    ];
}/*}}}*/

function work_wechat_get_jsapi_ticket()
{/*{{{*/
    static $ticket = null;

    if (is_null($ticket)) {
        $ticket = cache_get('work_wechat_jsapi_ticket');
    }

    if (empty($ticket)) {

        $res = _work_wechat_closure(function ($access_token) {
            return http_json(WORK_WECHAT_API_PREFIX.'/get_jsapi_ticket?'.http_build_query([
                'access_token' => $access_token,
            ]));
        });

        otherwise_error_code('WORK_WECHAT_CLIENT_EXCEPTION', work_wechat_if_res_success($res), [
            'code'    => $res['errcode'],
            'message' => $res['errmsg'],
        ]);

        $ticket = $res['ticket'];
        cache_set('work_wechat_jsapi_ticket', $ticket, ($res['expires_in'] - 1));
    }

    return $ticket;
}/*}}}*/

function work_wechat_get_app_jsapi_ticket()
{/*{{{*/
    static $ticket = null;

    if (is_null($ticket)) {
        $ticket = cache_get('work_wechat_jsapi_ticket');
    }

    if (empty($ticket)) {

        $res = _work_wechat_closure(function ($access_token) {
            return http_json(WORK_WECHAT_API_PREFIX.'/get_jsapi_ticket?'.http_build_query([
                'access_token' => $access_token,
            ]));
        });

        otherwise_error_code('WORK_WECHAT_CLIENT_EXCEPTION', work_wechat_if_res_success($res), [
            'code'    => $res['errcode'],
            'message' => $res['errmsg'],
        ]);

        $ticket = $res['ticket'];
        cache_set('work_wechat_jsapi_ticket', $ticket, ($res['expires_in'] - 1));
    }

    return $ticket;
}/*}}}*/
