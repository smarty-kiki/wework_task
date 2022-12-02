<?php

function get_work_wechat_client_user()
{/*{{{*/
    static $userid = null;

    if (is_null($userid)) {

        $userid = cookie('userid');
    }

    if (is_null($userid)) {
        $code = input('code');
        if (not_null($code)) {
            $res = work_wechat_get_user_info($code);

            if (work_wechat_if_res_success($res)) {
                otherwise_error_code('WORK_WECHAT_CLIENT_EXCEPTION', isset($res['userid']), [
                    'code' => -2,
                    'message' => '你还不是企业成员',
                ]);

                $userid = $res['userid'];
            }
        }
    }

    if (is_null($userid)) {
        trigger_redirect(work_wechat_build_oauth_redirect_url(uri()));
    }

    return $userid;
}/*}}}*/
