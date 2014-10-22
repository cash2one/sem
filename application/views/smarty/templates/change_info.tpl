{extends file="base.tpl"}
{block login_body}login-body{/block}
{block content}
<div class="wrapper-two boxshadow-bottom">
<div class="mymain clearfix">
<form class="form-horizontal changeinfo-form" action="">
    <div id="hzuserInput"></div>
    <div class="control-group">
        <label class="control-label">&nbsp;</label>
        <div class="controls">
            <button type="button" class="btn btn-block bg28be7a" id="changeHzuserInfo">保存</button>
        </div>
    </div>
	<p class="err-tips hide"></p>
</form>
</div>
</div>
{/block}
{block customjs append}
{tpl_change_hzuser}
{literal}
<script type="text/javascript">
$(function(){
    $("#hzuserInput").html(juicer($("#changeHzuserTpl").html(),hzUserInfoData));
    $("#changeHzuserInfo").on("click",function(){
        Setting.changeHzuserInfo();
    });
})
</script>
{/literal}
{/block}
