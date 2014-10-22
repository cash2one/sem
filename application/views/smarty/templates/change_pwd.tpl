{extends file="base.tpl"}
{block login_body}login-body{/block}
{block content}
<div class="wrapper-two boxshadow-bottom">
<div class="mymain clearfix">
<form class="form-horizontal changepwd-form" action="">
    <div class="control-group">
        <label class="control-label">当前密码</label>
        <div class="controls">
            <input class="m-wrap old-password" type="password" placeholder="当前密码" name="old_password"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">新密码</label>
        <div class="controls">
            <input class="m-wrap password" type="password" placeholder="新密码" name="password"/>
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
            <button type="button" class="btn btn-block bg28be7a" id="changePwd">修改密码</button>
        </div>
    </div>
	<p class="err-tips hide"></p>
</form>
</div>
</div>
{/block}
{block customjs append}
{literal}
<script type="text/javascript">
$(function(){
    $("#changePwd").on("click",function(){
        Setting.changePassword();
    });
})
</script>
{/literal}
{/block}
