{extends file="base.tpl"}
{block content}
<div class="modal modal-buy">
	<div id="buy_box">
	</div>
</div>


{/block}
{block customjs append}
{smart_template}
{literal}
<style>
body{overflow:hidden;}
#page_content{width:540px;height:532px;background-color:#ddd;}
</style>
{/literal}
<script type="text/javascript" src="/static/scripts/buy.js?v={$version}"></script>
{literal}
<script id="buy_pkg" type="text/template">
		<form id="buyForm" >
	<div class="modal-body">
		<p class="buy-pkg-hd">
			{@if type == 'first'}
				与你百度推广消费相似的用户，97%选择购买了<span>${recommend.bid_keyword_num}竞价词</span>
			{@else if type == 'up'}
				选择升级套餐
			{@else}
				套餐续费：<br/>
				<i>点击“确认续费”，客服人员将尽快联系你完成续费。</i>
			{@/if}
		</p>
		<input type="hidden" name="apply_type" value="{@if type=='first'}1{@else if type =='up'}2{@else}3{@/if}"/>
		<input type="hidden" name="apply_package" value="{@if type!='continue'}${recommend.id}{@else}${renew_pkg_info.id}{@/if}"/>
		<input type="hidden" name="user_id" value="${user_id}"/>
		<input type="hidden" name="req_type" value="apply"/>
		<div class="pkg-box {@if type != 'up'}pkg-box-a{@/if}">
			{@if len >3}
			<span class="prev" id="prev"><i></i></span>
			<span class="next" id="next"><i></i></span>
			{@/if}
			<div class="pkg-ul">
			<ul class="pkg-list transition" id="pkg_list" style="{@if num < 4}left:0;{@else}left:-240px;{@/if}{@if len < 3}width:100%{@/if}{@if len >= 3}text-align:left{@/if}">
{@if type == 'first'}
	{@each all_pkg_info as it}<li {@if it.recommend == 1}class="on"{@/if} pkginfo="${it.id},${it.bid_keyword_num},${it.money}">${it.bid_keyword_num}竞价词<br/><span class="font-blue">￥${it.money}/年</span> {@if it.recommend == 1}<i class="bid-icon recommend"></i>{@/if}</li>{@/each}
{@else if type == 'up'}
	{@each upgrade_pkg_info as it}<li {@if it.recommend == 1}class="on"{@/if} pkginfo="${it.id},${it.bid_keyword_num},${it.money},${it.total},${it.paid_total},${it.expiration_date}">${it.bid_keyword_num}竞价词<br/><span class="font-blue">￥${it.money}/年</span> {@if it.recommend == 1}<i class="bid-icon recommend"></i>{@/if}</li>{@/each}
{@else}
	<li class="on">${renew_pkg_info.bid_keyword_num}竞价词<br/><span class="font-blue">￥${renew_pkg_info.money}/年</span></li>
{@/if}
			</ul>
			</div>
		</div>
		<div class="choosed-pkg">
{@if type == 'first'}
			<table class="choosed-table">
				<tr>
					<td width="170"><i class="bid-icon ok-a"></i><span id="keyword_num">${recommend.bid_keyword_num}</span>竞价词</td>
					<td>有效期1年</td>
					<td class="col3">应付金额：<span>￥<span id="money">${recommend.money}</span></span></td>
				</tr>
			</table>
{@else if type == 'continue'}
			<table class="choosed-table">
				<tr>
					<td width="170"><i class="bid-icon ok-a"></i><span id="keyword_num">${renew_pkg_info.bid_keyword_num}</span>竞价词</td>
					<td>有效期1年</td>
					<td class="col3">应付金额：<span>￥<span id="money">${renew_pkg_info.money}</span></span></td>
				</tr>
			</table>
{@else}
			<table class="choosed-table">
				<tr>
					<td width="170"><i class="bid-icon ok-a"></i><span id="keyword_num">${recommend.bid_keyword_num}</span>竞价词</td>
					<td>有效期至<span id="expiration_date">${recommend.expiration_date}</span></td>
					<td class="col3"><label>应付金额：</label><span>￥<span id="money">${recommend.paid_total+recommend.total}</span></span></td>
				</tr>
				<tr class="up-tr">
					<td colspan="2"></td>
					<td class="col3 col3-border"><label>已购套餐退款：</label><span>-￥<span id="regund">${recommend.paid_total}</span></span></td>
				</tr>
				<tr class="up-tr">
					<td colspan="2"></td>
					<td class="col3 total"><label>总计：</label><span>￥<span id="total">${recommend.total}</span></span></td>
				</tr>
			</table>

{@/if}
		</div>
	<span class="err_tips hide"></span>
	</div>
	<div class="modal-footer">
		<button type="submit" class="blue-btn">{@if type != 'continue'}申请购买{@else}确认续费{@/if}</button>
		{@if type != 'first'}
		<button type="button" class="gray-btn pull-left" id="back">返回</button>	
		{@/if}
	</div>
	</form>
</script>
<script id="ok_hint" type="text/template">
	<div class="modal-body">
		<div class="buy-ok">
			<i class="bid-icon ok-b"></i>
			<p class="buy-ok-hd">
				${text}
			</p>
			<p class="buy-ok-bd">
				智投易客服专员会尽快与你联系，请耐心等待。<br/>如需变更竞价词套餐请告诉客服专员，谢谢！<br/>
				<a href="#" id="cat_result">查看详情 &gt;</a>
			</p>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="gray-btn buy-close">关闭</button>
	</div>
</script>
<script id="apply" type="text/template">
	<div class="modal-body">
		<table class="buy-table">
			<thead>
				<tr><td colspan="3">&nbsp;智投易账号：<b>${name}</b></td></tr>
			</thead>
			<tbody>
				{@if apply_status == 3 || apply_status == 2}
				<tr>
					<td width="210">&nbsp;当前套餐：${opened_package_info.bid_keyword_num}竞价词</td>
					<td>有效期至${opened_package_info.expiration_date}</td>
					<td {@if applying_package_info.apply_type == 3}class="applying rt"{@/if}>{@if apply_status == 2}<a href="#" id="continue">续费</a>{@/if}{@if applying_package_info.apply_type == 3}续费申请已提交{@/if}</td>
				</tr>
				{@/if}
				{@if applying_package_info.apply_type != 3}
				{@if apply_status == 1 || apply_status == 3}
				<tr>
					<td>&nbsp;升级申请：${applying_package_info.bid_keyword_num}竞价词</td>
					<td>有效期1年&nbsp;&nbsp;&nbsp;&nbsp; ￥${applying_package_info.money}</td>
					<td class="applying rt">等待客服确认</td>
				</tr>
				{@/if}
				{@/if}
			</tbody>
		</table>
		<div class="chart">
			<div class="chart-hd">竞价词使用情况</div>
			<div id="chart" class="chart-bd"></div>
			<div class="bid-pie hide" id="pie">
				<span class="use">已添加<br/>${chartdata['已添加']}<b></b></span>
				<span class="unuse">剩余可用<br/>${chartdata['剩余可用']}<b></b></span>
				<i class="f {@if parseInt(chartdata.percent*100) >= 50}right{@/if}"></i>
				<i style="-webkit-transform: rotate(${chartdata.percent*360}deg);"></i>
				<em class="f {@if parseInt(chartdata.percent*100) >= 50}right{@/if}"></em>
				<em style="-webkit-transform: rotate(${chartdata.percent*360}deg);"></em>
				<span class="num">${parseInt(chartdata.percent*100)}%</span>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		{@if apply_status == 2 && is_max != 1 }
		<button type="button" id="up" class="blue-btn">升级竞价词</button>
		{@/if}
		
		<button type="button" class="gray-btn buy-close">关闭</button>
	</div>
</script>
{/literal}
{/block}
