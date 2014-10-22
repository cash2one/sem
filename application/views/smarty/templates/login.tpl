{extends file="base.tpl" }
{block login_body}login-body{/block}
{block header}
{loginheader}
{/block}
{block content}
<div>
<!-- BEGIN LOGIN FORM -->
<form class="form-horizontal login-form" action="" id="login">
    <div class="space20"></div>
    <div class="control-group">
        <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
        <label class="control-label">用户名</label>
        <div class="controls">
            <input class="m-wrap" type="text" name="username" />
        </div>
    </div>
    <div class="control-group no-margin">
        <label class="control-label">密码</label>
        <div class="controls">
            <input class="m-wrap" type="password" name="password"  onpaste="return false"/><a href="/page/find_pwd" class="white">找回密码</a>
        </div>
    </div>
    <div class="space20"></div>
    <div class="control-group login-btn">
        <label class="control-label">&nbsp;</label>
        <div class="controls">
            <button type="submit" class="btn btn-block">登录</button>
        </div>
    </div>
	<!--div class="control-group">
        <div class="controls video-layer">
			<a href="http://static.haizhi.com/video/video.html" target="_blank"><i class="hzicon video"></i>由此进入智投易视频指引</a>
		</div>
	</div-->
	<p class="err-tips hide" style="margin-left:100px;">用户名和密码不能为空。</p>
</form>
<!-- END LOGIN FORM -->
</div>
{/block}
{block customjs}
<script type="text/javascript" src="/static/scripts/login.js?v={$version}"></script>
{/block}
