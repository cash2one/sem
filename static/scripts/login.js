/*
 * 登录
 */
function login(){
    var form = $(".login-form")[0];
    var username = $.trim(form.username.value);
    var password = $.trim(form.password.value);
    var $tip = $(".err-tips"),
		keep_login = '';

    if(username == ""){
        $tip.text("用户名不能为空").show();
        return false;
    }
    if(password == ""){
        $tip.text("密码不能为空").show();
        return false;
    }
    $tip.hide();

    $.ajax({
        type:'GET',
        url:'/hzuser/login',
        data:{"username":username,"password":password},
		dataType:'json',
        success:function(data){
            if(data.status=="success"){
                //bindAccount();
                if(data.mobile_verify == 1){//登录成功，已验证手机，去验证是否绑定账户等的提示页
                    location.href="/page/connection";
                }else{//登录成功，但是未验证手机
                    location.href = "/page/valid_mobile";
                }

            }else{
                var errorCode = data.error_code;
                switch(errorCode){
                    case "1":
                        $tip.text("账号或密码不能为空").show();
                        break;
                    case "2":
                        $tip.text("账号不存在").show();
                        break;
                    case "3":
                        $tip.text("账号没有权限").show();
                        break;
                    case "4":
                        $tip.text("账号被禁用").show();
                        break;
                    case "5":
                        $tip.text("账号或密码错误").show();
                        break;
                    case "6":
                        $tip.text("账号正在审核中").show();
                        break;
                    case "7":
                        $tip.text("账号审核被驳回").show();
                        break;
                }
            }
        }
    });
}

function showService(obj){

    $.ajax({
        type:"get",
        url:"/hzuser/owner_info",
        data:{},
        dataType:"json",
        success:function(data){
            if(data.status=="success"){
                var serviceTpl = $("#serviceTpl").html();
                var serviceHtml = juicer(serviceTpl,data);
                obj.append(serviceHtml);
            }
        }
    });
}
/*
function validMobileInit(){
        var vaildForm = $('#vaildForm'),$agreement = $('#agreement');
        $agreement.show();
        $agreement.click(function(e){
                var elem = e.target;
                if(elem.nodeName != 'INPUT') return;
                var type = elem.getAttribute('data');
                if(type == 'agree'){
                      $agreement.hide();
                      vaildForm.show(); 
                }else{
                    location.href ='/page/login';
                }
        })

}

*/
function bindAccount(){
    var data = getHzUserInfo();
    var $bind = $("#bindAccount"),
        $balance = $("#noBalance"),
        $exprise = $("#noTime"),
        $tip = $bind.find(".err-tips"),
        $agreement = $('#agreement');
    if(data.status=="success"){//已绑定，并且已充值

        var account = data.account,
            defaultBindUser = data.default_bind_user;

        if(account == 1){
            $balance.show();
            showService($balance);
            return false;
        }
        if(account == 3){
            $exprise.show();
            showService($exprise);
            return false;
        }

        if(defaultBindUser == null){ //没有绑定账号
            $tip.hide();
            //未看过服务条款
			/*
            if(data.agree == 1){
                    $agreement.show();
                    $agreement.click(function(e){
                        var elem = e.target;
                        if(elem.nodeName != 'INPUT') return;
                        var type = elem.getAttribute('data');
                        if(type == 'agree'){
                              $agreement.hide();
                                $bind.show(); 
                        }else{
                            location.href ='/page/login';
                        }
                    })
            }else{
                $bind.show(); 
            }
			*/
			$bind.show(); 
            $bind.find(".next").on("click",function(){
                var mediaType = $("#bindAccount").find("input[name=account_type]:checked").val(),
                    mediaName = "",
                    $mediaImg = $("#mediaImg");
                if(mediaType == "0"){
                     mediaName = "百度";
                     $mediaImg.attr({
                        "src":"/static/img/sem/baidu.png",
                        "alt":mediaName    
                     });
                }
                if(mediaType == "1"){
                     mediaName = "Google";
                     $mediaImg.attr({
                        "src":"/static/img/sem/google.png",
                        "alt":mediaName    
                     });
                }
                if(mediaType == "2"){
                     mediaName = "360搜索";
                     $mediaImg.attr({
                        "src":"/static/img/sem/360.png",
                        "alt":mediaName    
                     });
                }
                if(mediaType == "3"){
                     mediaName = "soso搜搜";
                     $mediaImg.attr({
                        "src":"/static/img/sem/soso.png",
                        "alt":mediaName    
                     });
                }
                    
                $bind.find(".step1").hide();
				location.href = "/page/connection";
                //$bind.find(".step2").show();
                $bind.find(".media-name").text(mediaName);
            });

			/*
            $("#btnBindAccount").off().on("click",function(){
                var form = $(".bind-form")[0];
                var type = $.trim(form.account_type.value);
                var username = $.trim(form.username.value);
                var password = $.trim(form.password.value);
                var $tip = $(".bind-form").find(".err-tips");
                if(username == ""){
                    $tip.text("请输入用户名。").show();
                    return false;
                }
				/*
                if(password == ""){
                    $tip.text("请输入密码。").show();
                    return false;
                }
                $tip.hide();
				location.href = "/page/connection";

                //需验证绑定账号是否正确
				/*
                $.ajax({
                    type:'GET',
                    url:'/hzuser/bind',
                    data:{"username":username,"password":password},
					dataType:'json',
                    success:function(data){
                        if(data.status=="success"){
                            //location.href = "/page/search_manage";
                            location.href = "/page/connection";
                        }else{
                            var errorCode = (data.error_code).toString(),
                                errorMsg = "";
                            switch(errorCode){
                                case "8409":errorMsg = "没有权限操作该用户";break;
                                case "8":errorMsg = "该账号已绑定";break;
                                case "9":errorMsg = "目前该代理商只能绑定一个账户";break;
                                case "10":errorMsg = "SEM API请求错误";break;
                                case "11":errorMsg = "未知错误";break;
                                case "12":errorMsg = "获取token错误";break;
                                case "13":errorMsg = "数据库读取失败";break;
                                default:errorMsg = "账号绑定失败";break;
                            }
                            $tip.text(errorMsg).show();
                            return false;
                        }
                    }
                });//end ajax

            });
	*/

            return false;
        }
        //location.href = "/page/search_manage";
        location.href = "/page/connection";

    }else{//请求海智用户信息失败
        var errorCode = data.error_code;
        switch(errorCode){
            case "1":
                $tip.text("您还没有登录").show();break;
            case "2":
                $tip.text("用户不存在").show();break;
        }
    }
}

/*
 * 发送手机验证码
 */
function sendValidCode(){
    var mobile = $.trim($(".mobile").val());
    var $tip = $(".err-tips");
    if(mobile==""){
        $tip.text("请输入手机号码").show();
        return false;
    }else{
        if(!RegTool.mobileReg.test(mobile)){
            $tip.text("手机号码格式错误").show();
            return false;
        }
    }
    $.ajax({
        type:'POST',
        url:"/hzuser/captcha_sms",
        dataType:"json",
        data:{"mobile":mobile},
        success:function(data){
            if(data.status=="success"){//验证成功，进入重置密码页
                $tip.text("验证码已成功发送到您的手机").show();
            }else{
                var errorCode = data.error_code,errorMsg = "";
                switch(errorCode){
                    case "1":errorMsg = "您还没有登录";break;
                    case "2":errorMsg = "该手机号码已经被绑定";break;
                    case "3":errorMsg = "验证码已经发送过，请注意查收";break;
                    case "4":errorMsg = "验证码发送失败";break;
                    case "5":errorMsg = "手机号码格式不对";break;
                    case "6":errorMsg = "改手机号码未注册";break;
                }
                $tip.text(errorMsg).show();
            }
        }
    });
}

/*
 * 登录时验证是否绑定手机
 * type:reset|find 验证通过后是重置密码还是找回密码
 */
function validMobile(type){
    var mobile = $.trim($(".mobile").val()),
        captcha = $.trim($(".captcha").val()),
        $tip = $(".err-tips");
    if(mobile == "" || !RegTool.mobileReg.test(mobile)){
        $tip.text("请输入正确的手机号码。").show();
        return false;
    }
    if(captcha == ""){
        $tip.text("请输入验证码。").show();
        return false;
    }

    $tip.hide();

    $.ajax({
        type:'POST',
        url:"/hzuser/captcha_verify",
        dataType:"json",
        data:{"captcha":captcha,"mobile":mobile},
        success:function(data){
            if(data.status=="success"){
                if(type == "reset"){
                    location.href = "/page/reset_pwd";
                }
                if(type == "find"){
                    $(".step2").show();
                    $(".step1").hide();
                }
            }else{
                var errorCode = data.error_code,errorMsg = "";
                switch(errorCode){
                    case "1":errorMsg = "您还没有登录";break;
                    case "2":errorMsg = "该手机号已经被绑定";break;
                    case "3":errorMsg = "验证码错误";break;
                    case "4":errorMsg = "验证码不能为空";break;
                    case "5":errorMsg = "手机号码格式不对";break;
                    case "6":errorMsg = "该手机号码未注册";break;
                }
                $tip.text(errorMsg).show();
            }
        }
    });
}


function resetPassword(type){
    var pwd = $.trim($(".password").val()),
        rePwd = $.trim($(".re-password").val()),
        $tip = $(".err-tips");

    if(pwd == ""){
        $tip.text("请输入密码。").show();
        return false;
    }
    if(rePwd != pwd){
        $tip.text("两次密码输入不一致。").show();
        return false;
    }
    $tip.hide();
    $.ajax({
        type:'GET',
        url:'/hzuser/reset_pwd',
        dataType:"json",
        data:{"password":pwd},
        success:function(data){
            if(data.status=="success"){
                if(type == "reset"){
                    location.href="/page/connection";
                    return false;
                }
                if(type == "find"){
                    location.href="/page/login";
                }
            }else{
                var errorCode = data.error_code;
                switch(errorCode){
                    case "1":
                        $tip.text("您还没有登录").show();break;
                    case "2":
                        $tip.text("手机号还没有绑定").show();break;
                    case "3":
                        $tip.text("密码重置失败").show();break;
                    case "4":
                        $tip.text("密码不能为空").show();break;
                    case "8":
                        $tip.text("您的账号已被注销").show();break;
                }
            }
        }
    });
}

$(function(){

    $("#login").on("submit",function(e){
	e.preventDefault();
        login();
    });
    $("#sendValidCode").on("click",function(){
        sendValidCode();
    });
    $("#bindMobile").on("click",function(){
        var type = $(this).attr("data");
        validMobile(type);
    });
    $("#resetPwd").on("click",function(){
        var type = $(this).attr("data");
        resetPassword(type);
    });
});
