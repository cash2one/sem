{extends file="base.tpl"}
{block login_body}login-body{/block}
{block header}
{loginheader}
{/block}
{block content}
<div>
<!-- BEGIN LOGIN FORM -->
<form class="form-horizontal password-form" action="">
    <div class="space20"></div>
    <div class="control-group">
        <label class="control-label">新密码</label>
        <div class="controls">
            <input class="m-wrap password" type="password" placeholder="密码" name="password"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">确认密码</label>
        <div class="controls">
            <input class="m-wrap re-password" type="password" placeholder="确认密码" name="re_password"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">&nbsp;</label>
        <div class="controls">
            <button type="button" class="btn btn-block" id="resetPwd" data="reset">提交</button>
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
})
</script>
{/literal}
{/block}
