{extends file="base.tpl"}
{block login_body}login-body{/block}
{block header}
{loginheader}
{/block}
{block content}
<div>
<!-- BEGIN LOGIN FORM -->
<form class="form-horizontal find-form" action="">
    <div class="space20"></div>
    <div class="step1">
        <div class="control-group">
            <label class="control-label">手机号码</label>
            <div class="controls">
                <input class="m-wrap mobile" type="text" placeholder="手机号码" name="mobile"/>
                <a href="javascript:;" class="white margin-top-10" id="sendValidCode">发送验证码</a>
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
                <button type="button" class="btn btn-block pull-left" id="bindMobile" data="find">确认验证码</button>
                <a href="/page/login" class="white pull-left margin-top-10">返回</a>
            </div>
        </div>
    </div>
    <div class="step2 hide">
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
                <button type="button" class="btn btn-block pull-left" id="resetPwd" data="find">提交</button>
                <a href="/page/login" class="white pull-left margin-top-10">返回</a>
            </div>
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
</script>
{/literal}
{/block}
