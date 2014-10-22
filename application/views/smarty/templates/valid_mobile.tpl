{extends file="base.tpl"}
{block login_body}login-body{/block}
{block header}
{loginheader}
{/block}
{block content}
<div>
<!-- BEGIN LOGIN FORM -->
<form class="form-horizontal valid-mobile-form" action="" id="vaildForm" >
    <div class="space20"></div>
    <div class="control-group">
        <label class="control-label">手机号码</label>
        <div class="controls">
            <input class="m-wrap mobile" type="text" placeholder="手机号码" name="mobile" disabled />
            <button type="button" class="btn" id="sendValidCode">发送验证码</button>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">验证码</label>
        <div class="controls">
            <input class="m-wrap captcha" type="text" placeholder="验证码" name="captcha"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">&nbsp;</label>
        <div class="controls">
            <button type="button" class="btn btn-block" id="bindMobile" data="reset">提交</button>
        </div>
    </div>
    <p class="err-tips hide" style="margin-left:100px;"></p>
</form>
<!-- END LOGIN FORM -->
</div>
{/block}
{block customjs}
{literal}
<script type="text/javascript" src="/static/scripts/login.js"></script>
<script type="text/javascript">
$(function(){
    hzUserInfoData = getHzUserInfo();
    $(".nickname").text(hzUserInfoData.name);
    $(".mobile").val(hzUserInfoData.mobile);
    //validMobileInit();
})
</script>
{/literal}
{/block}
