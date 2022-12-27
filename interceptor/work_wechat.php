<?php

const USERID_COOKIE_NAME = 'user_id';

function get_work_wechat_client_user()
{/*{{{*/
    static $user_id = null;

    if (is_null($user_id)) {

        $user_id = cookie(USERID_COOKIE_NAME);
        log_module('work_wechat', '从 cookie 中获取 user_id: '.$user_id);
    }

    if (is_null($user_id)) {
        $code = input('code');
        if (not_null($code)) {
            log_module('work_wechat', '通过 code 获取用户信息: '.$code);
            $res = work_wechat_get_user_info($code);

            if (work_wechat_if_res_success($res)) {
                otherwise_error_code('WORK_WECHAT_CLIENT_EXCEPTION', isset($res['userid']), [
                    'code' => -2,
                    'message' => '你还不是企业成员',
                ]);

                log_module('work_wechat', '通过 code 获取用户信息 user_id: '.$user_id);
                $user_id = $res['userid'];
                setcookie(USERID_COOKIE_NAME, $user_id, time() + 3600 * 24 * 30, '/');
            }
        }
    }

    if (is_null($user_id)) {
        trigger_redirect(work_wechat_build_oauth_redirect_url(uri()));
    }

    return $user_id;
}/*}}}*/
