<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>任务达人</title>
</head>
<body>

<script src="//res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
<script>
wx.ready(function(){
    wx.invoke("selectEnterpriseContact", {
        "fromDepartmentId": -1,
        "mode": "single",
        "type": ["user"],
        "selectedDepartmentIds": ["{{ $user_id }}"],
    },function(res){
        if (res.err_msg == "selectEnterpriseContact:ok")
        {
            if(typeof res.result == 'string')
            {
                res.result = JSON.parse(res.result);
            }

            var selectedUserList = res.result.userList;
            for (var i = 0; i < selectedUserList.length; i++)
            {
                var user = selectedUserList[i];
                var userId = user.id;
                var userName = user.name;
                var userAvatar= user.avatar;
            }
        }
    }
);

});
wx.error(function(res){ });

wx.config({
    beta: true,
    debug: true,
    appId: '{{ $config["corpid"] }}',
    timestamp: {{ $signature_info['timestamp'] }},
    nonceStr: {{ $signature_info['nonce_str'] }},
    signature: {{ $signature_info['signature'] }},
    jsApiList: []
});
</script>
</body>
</html>
