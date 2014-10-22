{extends file="base.tpl"}
{block content}
<div class="page-content">
<!-- BEGIN PAGE CONTAINER-->
<div class="wrapper-left boxshadow-bottom no-margin" id="page_content">
	<!-- 左侧树(账户结构)-->
	{bidLeft}
    <div class="main transition">
	{errTipsLayer}
	<div id="competitor_expiration" class="competitor-tip hide"></div>
	<div class="tabbable boxshadow-bottom br3">
		<div class="filter-layer" id="filter_layer">
			<form action="" method="" class="form-horizontal" id="smartFilterForm">
				<input id="skeyword" name="keyword" type="text" placeholder="关键词搜索" /><span class="search-btn" id="search"></span>
			</form>
		</div>
		<div class="btn-bar g" id="portlet_tab2">
			<a data-toggle="modal" href="#" class="g-u boxshadow-bottom btn-open calc-bid"><i class="hzicon calc"></i>计算出价</a>
			<div class="btn-group tag">
				<a href="javascript:;" class="btn btn-border g-u dropdown-toggle boxshadow-bottom" data-toggle="dropdown">添加标签<i class="hzicon angle-down"></i></a>
				<ul class="dropdown-menu g">
				<li class="tag-list">
				</li>
				<li><a href="#" type="newtag">新建标签</a></li>
				<li><a href="#" type="outtag">移除标签</a></li>
				</ul>
			</div>
			<div class="g-u tagtips-box">
				<i class="hzicon tagtips tooltips" data-placement="bottom" data-original-title="为了显著提升关键词的竞价效果。请为重要的竞价关键词添加<span class='err'>“核心竞价词”</span>标签，该标签支持60个关键词，感谢使用智投易系统，祝您使用愉快！"></i>
			</div>
		</div>
	</div>
	<div class="tab-content g-change" id="tab_content">
		{smart_bid}
	</div><!--end tab-content-->
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
	sem.pageInitData('ref');
	sem.page = 'ref';
})
</script>
{/literal}
{/block}
