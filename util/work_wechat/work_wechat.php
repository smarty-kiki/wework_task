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
        log_module(WORK_WECHAT_LOG_MODULE, print_r($res, true));
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
    $config = config('work_wechat');

    return _work_wechat_closure(function ($access_token) use ($code) {
        log_module(WORK_WECHAT_LOG_MODULE, print_r($access_token, true));
        $a = http_json(WORK_WECHAT_API_PREFIX.'/getuserinfo?'.http_build_query([
            'access_token' => $access_token,
            'code'         => $code,
        ]));
        log_module(WORK_WECHAT_LOG_MODULE, print_r($a, true));
        return $a;
    });
}/*}}}*/

function work_wechat_build_oauth_redirect_url($url)
{/*{{{*/
    $work_wechat_config = config('work_wechat');

    return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$work_wechat_config['corpid'].'&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_base&state=1#wechat_redirect';
}/*}}}*/
