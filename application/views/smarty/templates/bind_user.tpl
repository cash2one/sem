{extends file="base.tpl"}
{block login_body}login-body{/block}
{block content}
<div>
<!-- BEGIN LOGIN FORM -->
<form class="form-horizontal bind-form" action="">
    <div class="hide" id="noBalance">
        <h3 class="form-title no-indent"><i class="hzicon notice-big"> </i>&nbsp;您的余额不足！请及时联系客服进行充值。</h3>
        <div class="space20"></div>
    </div>
    <div class="hide" id="noTime">
        <h3 class="form-title no-indent"><i class="hzicon notice-big"> </i>&nbsp;您的使用时间已截止，请联系客服重新购买</h3>
        <div class="space20"></div>
    </div>
    <div class="hide" id="bindAccount">
        <div class="step1">
            <h3 class="form-title no-indent">请选择搜索媒体</h3>
            <div class="space20"></div>
            <div class="control-group">
                <div class="controls">
                    <label class="media-label">
                        <input type="radio" name="account_type" value="0" checked />
                        <img src="/static/img/sem/baidu.png" alt="百度">
                    </label>
                    <label class="media-label">
                        <input type="radio" name="account_type" value="1" disabled />
                        <img src="/static/img/sem/google_disable.png" alt="Google">即将开放
                    </label>
                    <label class="media-label">
                        <input type="radio" name="account_type" value="2" disabled />
                        <img src="/static/img/sem/360_disable.png" alt="360搜索">即将开放
                    </label>
                    <label class="media-label">
                        <input type="radio" name="account_type" value="3" disabled />
                        <img src="/static/img/sem/soso_disable.png" alt="soso搜搜">即将开放
                    </label>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">&nbsp;</label>
                <div class="controls">
                    <button type="button" class="btn btn-block bg28be7a next">下一步</button>
                </div>
            </div>
        </div>
        <div class="step2 hide">
            <h3 class="form-title no-indent">请输入<span class="media-name">百度</span>推广账号用户名</h3>
            <div class="space20"></div>
            <div class="control-group">
                <label class="control-label">&nbsp;</label>
                <div class="controls">
                    <img id="mediaImg" src="" alt="">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">百度推广账号</label>
                <div class="controls">
                    <input class="m-wrap" type="text" placeholder="用户名" name="username"/>
                </div>
            </div>
            <div class="control-group">
                <div class="controls font-gray" style="width:auto">
                    <!--input class="m-wrap" type="password" placeholder="密码" name="password"/-->
					<i class="hzicon notice-ssmall"></i>系统绑定百度推广账号后，如需变更请联系您的客服专员
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">&nbsp;</label>
                <div class="controls">
                    <button type="button" class="btn btn-block" id="btnBindAccount">确认</button>
                </div>
            </div>
        </div>
        <p class="err-tips hide" style="margin-left:100px;"></p>
    </div>
</form>
<!-- END LOGIN FORM -->
</div>
{/block}
{block customjs }
{literal}
<script type="text/javascript" src="/static/scripts/login.js"></script>
<script type="text/javascript">
$(function(){
    hzUserInfoData = getHzUserInfo();
    $(".nickname").text(hzUserInfoData.name);
   // bindAccount();
    
})
</script>
{/literal}
{tpl_service}
{/block}
