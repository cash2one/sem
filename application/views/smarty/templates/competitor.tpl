{extends file="base.tpl"}
{block content}
<div class="page-content">
<!-- BEGIN PAGE CONTAINER-->
<div class="wrapper-left boxshadow-bottom no-margin" id="page_content">
	<!-- 左侧树(账户结构)-->
	{bidLeft}
    <div class="main transition">
		{errTipsLayer}
        <div class="tabbable boxshadow-bottom br3">
            <ul class="tabs g" id="nav_tabs">
               <li><a type="competitor">对手跟踪</a></li>
            </ul>
			<div id="competitor_expiration" class="competitor-tip hide"></div>
            <div class="clearfix" id="filter_layer">
                <form action="" method="" class="form-horizontal smartFilterForm" id="smartFilterForm">
                    <div class="control-group">
                        <div class="controls no-margin">
                            <div class="input-prepend input-append">
                                <input class="m-wrap br4 hzicon" id="skeyword" name="keyword" type="text" placeholder="请输入关键词" /><span class="btn" id="search">搜索</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-content g-change" id="tab_content">
                {smart_bid}
            </div><!--end tab-content-->
        </div><!--end tabbable-->
        <div id="modalOpenBidLayer"></div>
        {update_account}
    </div><!--end main-->
</div><!--end wrapper-left-->
<!-- END PAGE -->
</div>
{modal_accounterror}
{/block}
{block customjs append}
{smart_template}
<script type="text/javascript" src="/static/scripts/smart.js?v={$version}"></script>
{literal}
<script>
$(function(){
	sem.pageInitData('competitor');
	sem.page = 'competitor';
})
</script>
{/literal}
{/block}
