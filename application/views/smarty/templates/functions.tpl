{function header}
<div class="header navbar">
    <!-- BEGIN TOP NAVIGATION BAR -->
    <div class="navbar-inner">
    <div class="container-fluid">
        <a class="brand" href="/page/search_manage">
            <img src="/static/img/sem/logo.png" height="90" width="170" alt="logo" />
        </a>
        <a href="javascript:;" class="btn-navbar collapsed" data-toggle="collapse" data-target=".nav-collapse">
            <img src="/static/img/menu-toggler.png" alt="" />
        </a>
        <div class="navbar">
                <ul class="nav pull-left main-nav">
                    <li>
                        <a href="/page/smart_bid">智能竞价</a>
                    </li>
                    <li>
                        <a href="/page/ref_bid">出价参考</a>
                    </li>
                    <li>
                        <a href="/page/competitor">对手跟踪</a>
                    </li>
                </ul>
        </div><!--end hor-menu-->
        <ul class="nav pull-right sub-nav">
            <li class="hotline"><i class="hzicon"></i><div class="bd"><span>免费客服热线</span><br/><span class="ft">400 063 9966</span></div></li>
            <li class="hotqq"><i class="hzicon"></i><div class="bd"><span>QQ在线咨询</span><br/><a class="ft" target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=2796167227&site=qq&menu=yes">2796167227</a></div></li>
            <li><a href="/page/change_info"><i class="hzicon user"></i> <span class="nickname">nickname</span></a></li>
            <li><a href="/page/help"><i class="hzicon help"></i> 帮助</a></li>
            <li><a href="#" id="logout"><i class="hzicon exit"></i> 注销</a></li>
        </ul>
    </div>
    </div>
    <!-- END TOP NAVIGATION BAR -->
</div><!--END HEADER-->
{/function}
{function loginheader}
<div class="header navbar">
    <!-- BEGIN TOP NAVIGATION BAR -->
    <div class="navbar-inner">
    <div class="container-fluid">
        <a class="brand" href="/page/search_manage">
            <img src="/static/img/sem/logo.png" height="90" width="170" alt="logo" />
        </a>
        <ul class="nav pull-right sub-nav">
            <li><a href="/page/login"><i class="hzicon user"></i> <span class="nickname">登录</span></a></li>
        </ul>
    </div>
    </div>
    <!-- END TOP NAVIGATION BAR -->
</div><!--END HEADER-->
{/function}
{function footer}
<!-- BEGIN FOOTER -->
<div class="footer clearfix">
    <div class="footer-inner text-center">
        Copyright &copy; 2014 海智网聚网络技术（北京）有限公司.
    </div>
</div>
<!-- END FOOTER -->
{/function}
{function myleft}
<div class="cleft">
    <div class="portlet blue">
        <div class="portlet-title">
            <div class="caption">个人设置</div>
        </div>
        <div class="portlet-body">
            <ul class="tree set-nav">
                <li><p {if $focus == 0}class="on"{/if}><a href="/page/change_info">修改用户信息</a></p></li>
                <li><p {if $focus == 1}class="on"{/if}><a href="/page/change_pwd">修改密码</a></p></li>
                <li><p {if $focus == 2}class="on"{/if}><a href="/page/customer_access">客服授权</a></p></li>
            </ul>
        </div>
    </div>
</div>
{/function}
{function helpleft}
<div class="cleft help-nav">
	<div class="box" id="help_tree" >
		<div class="hd">开始使用智投易</div>
		<div class="portlet-body">
			<ul class="help-tree">
				<li class="bid-icon add {if $page =='add'}on{/if}">
					<a href="/page/help">添加竞价词</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon del {if $page =='del'}on{/if}">
					<a href="/page/help_del">删除竞价词</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon modify {if $page =='modify'}on{/if}">
					<a href="/page/help_modify">修改竞价设置</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon status {if $page =='status'}on{/if}">
					<a href="/page/help_status">修改竞价状态</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon tag {if $page =='tag'}on{/if}">
					<a href="/page/help_tag">分组管理</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon rank {if $page =='rank'}on{/if}">
					<a href="/page/help_rank">实时排名</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon monitor {if $page =='monitor'}on{/if}">
					<a href="/page/help_monitor">排名监控</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon feedback {if $page =='feedback'}on{/if}">
					<a href="/page/help_feedback">竞价反馈</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon log {if $page =='log'}on{/if}">
					<a href="/page/help_log">竞价日志</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon white-list {if $page =='white_list'}on{/if}">
					<a href="/page/help_white_list">设置白名单</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon bid {if $page =='bid'}on{/if}">
					<a href="/page/help_bid">24小时竞价</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon sync {if $page =='sync'}on{/if}">
					<a href="/page/help_sync">数据同步</a>
					<span class="arrow"></span>
				</li>
				<li class="bid-icon accounts {if $page =='accounts'}on{/if}">
					<a href="/page/help_accounts">多账号登录</a>
					<span class="arrow"></span>
				</li>
			</ul>
		</div><!--end portlet-body-->
	</div><!--end portlet-->
</div>
{/function}
{function help_service}
<div class="service">
	<ul>
		<li class="bid-icon phone">400 063 9966 </li>
		<li class="bid-icon qq">2796 167 227</li>
	</ul>
</div>
{/function}
{function errTipsLayer}
		<div class="alert clearfix margin-top-20 hide" id="tagtips">
			<span class="tipicon"><i class="hzicon notice-small"></i></span>
			为了显著提升关键词的竞价效果。请为重要的竞价关键词添加<span class="notice">“核心竞价词”</span>标签，该标签支持60个关键词，感谢使用智投易系统，祝您使用愉快！
		</div>
		<div class="alert clearfix margin-top-20 hide" id="competitortips">
			<span class="tipicon"><i class="hzicon notice-small"></i></span>
			您好，感谢您使用智投易，<span class="notice">"对手跟踪"</span>是智投易系统付费功能，请联系您的客服为您开通，祝您使用愉快
		</div>
	<div class="account-error">
		<div class="alert account-error-1 hide clearfix margin-top-20">
			<span class="tipicon"><i class="hzicon notice-small"></i></span>
			百度API服务器响应异常，暂时无法进行正常操作。<br/>我们正在不断尝试连接百度API服务器，待服务恢复后将会第一时间完成对接，如有其他疑问请联系您的客服。<br />当前数据更新时间：<span class="account-last-update"></span>
		</div>
		<div class="alert account-error-2 hide clearfix margin-top-20">
			<span class="tipicon"><i class="hzicon notice-small"></i></span>
			<span class="nickname">海智智投易</span> 您好！由于您的<span class="account-name">SEM</span>推广账户密码错误，智投易系统获取账户数据失败，请重新<a data-toggle="modal" href="#accountReLogin">输入账户密码</a>更新账户数据。<br />当前数据更新时间：<span class="account-last-update"></span>
		</div>
		<div class="alert account-error-3 hide clearfix margin-top-20">
			<span class="tipicon"><i class="hzicon notice-small"></i></span>
			<span style="display:inline-block;margin-top:8px;"><span class="nickname">海智智投易</span> <span class="balance-tips">好！您当前账户金额预计可消费<span class="consume-days">0</span>天，账户余额不足将会影响正常竞价和推广，请及时充值！</span></span>
		</div>
		<div class="alert account-error-4 hide clearfix margin-top-20">
			<span class="tipicon"><i class="hzicon notice-small"></i></span>
			<span class="pause-tips" style="display:inline-block;margin-top:8px;"></span>
		</div>
	</div><!--end account-error-->
{/function}
{function modal_accounterror}
<div id="accountReLogin" class="modal hide fade">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h3>智投易 - 推广账号</h3>
    </div>
    <div class="modal-body">
            <div class="alert alert-error hide">
                <button class="close" data-dismiss="alert"></button>
                <span>密码不能为空。</span>
            </div>
            <div class="control-group">
                <label class="control-label">用户名</label>
                <div class="controls">
                    <span class="account-name"></span>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">密码</label>
                <div class="controls">
                    <div class="left">
                        <input class="m-wrap placeholder-no-fix password" type="password" placeholder="密码" name="password"/>
                    </div>
                </div>
            </div>
    </div><!--end modal-body-->

    <div class="modal-footer">
        <button type="button" class="btn green" id="btnReLogin">登录</button>
    </div>
</div><!--end ip-->
{/function}
{function update_account}
<div id="updateAccountData" class="modal hide">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h3>更新账户信息</h3>
    </div>
    <div class="modal-body">
        <div class="modal-left"><i class="hzicon notice-big"></i></div>
        <div class="modal-right">
            <p class="modal-hd">确定更新账户信息吗？</p>
            <p class="modal-bd">上次数据更新时间：<span class="account-last-update"></span></p>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn sure" id="btnUpdateAccountData">确定</button>
        <button type="button" data-dismiss="modal" class="btn">取消</button>
    </div>
</div>
{/function}
{function white_list}
<div id="whiteList_set" class="modal hide">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h3>设置“<span class="bindUsername">测试账号1</span>”竞价白名单</h3>
    </div>
    <div class="modal-body white-modal">
	<form action="/user/add_whitelist" method="get" id="addWhitelistForm" >
		<div class="control-group">
			<label class="control-label">添加白名单网址</label>
			<div class="controls">
				<input type="text" class="m-wrap" name="domains" maxlength="200"/>
				<input type="submit" class="btn sure" value="添加" />
				<a href="javascript:;" id="whiteCheck" target="_blank" class="white sure">校验</a>
			</div>
			<span id="err_white" class="err"></span>
		</div>
	</form>
		<div class="control-group white-list" id="white_list"></div>
		<div class="issue-list">
		    <p class="hd">什么是白名单</p>
		    <p class="bd">在竞价广告位上展示的广告来自白名单中的任何一个网址时，智能竞价服务将自动调低该竞价词的期望排名，避免与之相互竞争。</p>
		</div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn">关闭</button>
    </div>
</div>
{/function}
{function modal}
<div id="modal" class="modal hide" data-backdrop="static"></div>
{/function}

{function tpl_pager}
{literal}
        <script id="pagerTpl" type="text/template">
        <div class="pager-box"> 
			<div class="bd">
			<p class="page-size">20个/页 共${count}个竞价词</p>
			<select class="hide">
					<option value="20"{@if page_size == 20}selected{@/if}>20个/页</option>
			</select>
			<div class="pagination pagination-right" >
			<ul>
					<li class="disabled f"><span>第<span class="num">${cur_page}</span>页/共${total_page}页</span></li>
					<li class="l{@if prev_page < 1} disabled{@/if}"><a href="#" class="boxshadow-bottom" index="${prev_page}">上一页</a></li>
					<li  class="r{@if next_page > total_page} disabled{@/if}"><a href="#" class="boxshadow-bottom" index="${next_page}">下一页</a></li>
			</ul>
			</div>
			</div>
        </div>
        </script>
{/literal}
{/function}
{function tpl_modalHint}
{literal}
<script id="modalHintTpl" type="text/template">
	    <div class="modal-header">
				<h3>提示</h3>
	    </div>
	    <div class="modal-body">
			<div class="modal-left"><i class="hzicon notice-big"></i></div>
            <div class="modal-right">
                <p class="hint margin-top-10">${text}</p>
            </div>	
		</div><!--end modal-body-->
	    <div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn sure">确定</button>
	    </div>
</script>
{/literal}
{/function}
{function tpl_more100}
{literal}
<script id="more100Tpl" type="text/template">
	<div class="modal-header">
		<h3>提示</h3>
	</div>
	<div class="modal-body">
		<div class="modal-left"><i class="hzicon notice-big"></i></div>
		<div class="modal-right">
			<p class="hint">为了给您带来更加快速、极致的竞价体验。智投易产品全新升级，最多可支持${num}个关键词进行极速竞价。</p>
			<p class="font-hint">
				系统检测您的竞价词已超过${num}个，请选择需要保留的${num}个竞价词
			</p>
		</div>	
	</div><!--end modal-body-->
	<div class="modal-footer">
			<button type="button" data-dismiss="modal" id="more100_btn" class="btn sure">确定</button>
	</div>
</script>
{/literal}
{/function}
{function tpl_checkMore100}
{literal}
<script id="checkMore100Tpl" type="text/template">
	<div class="modal-header">
		<h3>提示</h3>
	</div>
	<div class="modal-body">
		<div class="modal-left"><i class="hzicon notice-big"></i></div>
		<div class="modal-right">
			<p class="hint">竞价词已满${num}个,当前最多可添加${num}个竞价词。</p>
			<p>
				如需添加更多竞价词，你可以：<br/>
				1.删除部分竞价词，重新添加。<br/>
				2.<a class="buy-open">马上升级竞价次数</a>。
			</p>
		</div>	
	</div><!--end modal-body-->
	<div class="modal-footer">
			<button type="button" data-dismiss="modal" class="btn sure">确定</button>
	</div>
</script>
{/literal}
{/function}
{function tpl_keepKeywords}
{literal}
<script id="keepKeywordsTpl" type="text/template">
<div class="modal hide" id="keepKeywords" data-backdrop="static">
	<div class="modal-header">
		<h3>选择需要保留的竞价词</h3>
	</div>
	<div class="modal-body no-padding">
		<div class="keyword-count">
			<p class="pull-right">放弃保留关键词，<a class="re-check" href="#" id="re_check">点此重新添加竞价词</a></p>
			<p class="font-hint">智投易最多可添加100个竞价词。</p>
			<p>已选保留：<span class="bold"><span id="keepCount">0</span> / 100</span></p>
		</div>
		<div class="choose-bd">
		<div class="left-menu" id="menu">
			<ul class="level-plan">
				{@each plan as p}
				<li class="plan-li">
					<p count="${p.keyword_count}" type="plan" id="${p.id}" class="cursor level-one">
					    <i class="hzicon folder-closed" type="switch"></i>${p.name}</p>
					<ul class="level-unit hide">
						{@each p.unit as u}
							<li class="cursor" id="${u.id}" type="unit" count="${u.keyword_count}">${u.name}</li>
						{@/each}
					</ul>
				</li>
				{@/each}
			</ul>
		</div>
		<div class="keyword-body" id="keyword_bd">
			<div class="hd">
			    <span id="current_plan" class="bold"></span>
			</div>
			<div class="bd">
				<ul id="keyword_list"></ul>
			</div>
			<div class="ft"><label class="inline" for="all"><input type="checkbox" name="all" value=""/> 全选</label></div>
		</div>
		</div>
		<p class="err_tips hide"></p>
	</div><!--end modal-body-->
	<div class="modal-footer">
			<button type="button" id="keepKeyword_btn" class="b-btn">确定保留</button>
	</div>
	</div>
</script>
{/literal}
{/function}
{function tpl_modal_handle}
{literal}
<script id="modalHandleTpl" type="text/template">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h3>${title}</h3>
    </div>
	<form action="${url}" method="get" id="hindleForm">
	    <input type="hidden" name="user_id" value="${user_id}"/>
	    <input type="hidden" name="${name}" value="${ids}"/>
	{@if name == 'creative_ids'}
		{@if type !== 'del'}
		<input type="hidden" name="type" value="2" />
		<input type="hidden" name="fields" value="${field}" />
		<input type="hidden" name="str_to" value="${value}" />
		{@/if}
	{@else if field !== ''}
		{@if pause !== ''}
		<input type="hidden" name="pause" value="${pause}"/ >
		{@/if}
		{@if field !== ''}
		<input type="hidden" name="field" value="${field}" />
		<input type="hidden" name="value" value="${value}" />
		{@/if}
    {@else}
		<input type="hidden" name="${param}" value="${value}" />
	{@/if}
    <div class="modal-body">
        <div class="modal-left">
            <i class="hzicon notice-big"></i>
        </div>
        <div class="modal-right">
	        {@if text_b !== ''}
            <p class="text_a">${text_a}</p>
            <p class="text_b modal-bd">${text_b}</p>
            {@else}
            <p class="text_a margin-top-10">${text_a}</p>
	        {@/if}
        </div>
    </div><!--end modal-body-->
    <div class="modal-footer">
        <button type="submit" class="btn sure">确定</button>
        <button type="button" data-dismiss="modal" class="btn">取消</button>
	    <span id="err"></span>
    </div>
	</form>
</script>
{/literal}
{/function}
{function tpl_service}
{literal}
<script id="serviceTpl" type="text/template">
<div class="control-group">
    <p class="service">您的归属客服信息：</p>
    <div class="bgfff boxshadow-bottom service-box">
        <dl>
        <dd><span class="hd">联系人</span>${contact}</dd>
        <dd><span class="hd">联系方式</span>${mobile}</dd>
        <dd><span class="hd">电子邮箱</span><a href="mailto:${email}">${email}</a></dd>
        </dl>
    </div>
</div>
</script>
{/literal}
{/function}
{function tpl_change_hzuser}
{literal}
<script id="changeHzuserTpl" type="text/template">
    <div class="control-group">
        <label class="control-label">手机号码</label>
        <div class="controls">
            <p class="inline" style="line-height:38px;font-size:14px;">${mobile}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label"><span class="err-tips">*</span>联系人</label>
        <div class="controls">
            <input class="m-wrap contact" type="text" value="${contact}"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label"><span class="err-tips">*</span>电子邮箱</label>
        <div class="controls">
            <input class="m-wrap email" type="text" value="${email}"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label"><span class="err-tips">*</span>企业名称</label>
        <div class="controls">
            <input class="m-wrap name" type="text" value="${name}"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label"><span class="err-tips">*</span>企业地址</label>
        <div class="controls">
            <textarea class="m-wrap address">${address}</textarea>
        </div>
    </div>
</script>
{/literal}
{/function}

{*smart bid start*}
{function smart_bid}
	<div class="tab-list">
		<ul id="tagMenu">
		</ul>
	</div>
	<div id="autobid_list"></div>
{/function}
{*smart bid end*}
{function tpl_smartBidList}
{literal}
<script type="text/template" id="smartBidListTpl">
    <table class="table margin-top-10">
        <thead>
            <tr>
                <th class="first"><input type="checkbox" name="all" value="" /></th>    
                <th>竞价词</th>
                <th>竞价词状态</th> 
                <th>最新出价</th>
                <th>实时排名</th>
                <th>出价上限</th>
                <th>期望排名</th>
                <th>竞价地域</th>
                <th class="last" style="min-width:160px"></th>
            </tr>
        </thead>
        <tbody>            
	{@if list.length > 0}
		{@each list as it,index}
		    <tr class="{@if index%2 == 0}odd{@else}even{@/if} {@if it.bid_status == 3 || it.pause_autobid == 1}stop{@/if}" id="tr_${it.keyword_id}">
			<td>
                <input type="checkbox" name="ids" value="${it.keyword_id}" region="${it.region}" min="${it.min_bid}" max="${it.max_bid}" rank="${it.target_rank}" bidarea="{@if it.bid_area != '--'}${it.bid_area}{@/if}" strategy="${it.strategy}" snipe="${it.snipe}" snipe_domain="${it.snipe_domain}" snipe_strategy="${it.snipe_strategy}" snipe_without_strategy="${it.snipe_without_strategy}" bid_status="${it.bid_status}" />
                <input type="hidden" class="status-hide" value="{@if it.belong_plan.pause == 0 && it.belong_unit.pause == 0 && it.pause == 0}1{@else}0{@/if}" />
                <input type="hidden" class="creative-hide" value="{@if it.belong_unit.creative_count > 0}1{@else}0{@/if}" />
            </td>
			<td>
			    <div class="cog-active open-bid" data="single">
                    ${it.keyword}<i class="hzicon cog-blue invisible"></i>
                </div>
			</td>
			<td class="bid-status">
			{@if it.bid_status == 2}
				{@if it.pause_autobid == 0}
					<span class="bid-icon-status bid-open refresh">
						<span class="bid-icon-text">竞价中</span>
						<span class="bid-btn bid-operate"><i class="hzicon"></i></span>
					</span>
				{@else if it.pause_autobid == 2}
					<span class="bid-icon-status bid-open bid-waiting refresh">
						<span class="bid-icon-text">等待竞价</span>
						<span class="bid-btn bid-operate"><i class="hzicon"></i></span>
					</span>
				{@else}
					<span class="bid-icon-status bid-open bid-stop refresh">
						<span class="bid-icon-text">关键词停投</span>
						<span class="bid-btn bid-operate"><i class="hzicon"></i></span>
					</span>
				{@/if}
			{@else if it.bid_status == 3}
					<span class="bid-icon-status bid-pause">
                        <span class="bid-icon-text">竞价暂停</span>
                        <span class="bid-btn bid-operate"><i class="hzicon"></i></span>
                    </span>
			{@/if}
			{@if it.snipe == 1}
			<i class="hzicon snipe"></i>
			{@/if}
			</td>
			<td class="price opacity">
			<span class="refresh">
			${it.price}</td>      
			</span>
			<td class="opacity">
			<span class="refresh">
				<span class="rank">
				{@if it.rank > 10 && it.rank <30}
				<i type="photo" class="cursor photo" keyword="${it.keyword}" rank="${it.rank}" target_rank="${it.target_rank}">
					{@if it.rank < 20}
					${it.rank.replace(/^(.)/,'左')}
					{@else}
					${it.rank.replace(/^(.)/,'右')}
					{@/if}
					{@if it.rank_tip}
					<span class="tips err_tips">点击核对实时排名<i></i></span>
					{@/if}
				</i>
				{@else}
					--
				{@/if}
				</span>
				{@if it.show_monitor == 1}
				<span class="monitor"><i class="bid-icon rate" type="rank_monitor" snipe="${it.snipe}" keyword="${it.keyword}" keywordid="${it.keyword_id}"></i></span>
				{@/if}
			</span>
			</td> 
			<td class="opacity">
				${it.max_bid}
			</td> 
			<td class="opacity">
            {@if it.snipe == 0}
				{@if it.target_rank > 10 && it.target_rank <30}
					{@if it.target_rank < 20}
					${it.target_rank.replace(/^(.)/,'左')}
					{@else}
					${it.target_rank.replace(/^(.)/,'右')}
					{@/if}
				{@else}
					--
				{@/if}
            {@else if it.snipe_strategy == '1'}
                    超越对手
            {@else} 
                    跟随对手
            {@/if}
		    </td> 
			<td class="opacity">
				{@if it.bid_area !== '--'}
					${it.bid_area_name}
				{@else}
					${it.bid_area}
				{@/if}
			</td>                  
			<td class="feedback-td opacity">
				<span class="refresh">
				{@if it.complete_feedback > 1}
					<i class="time">${it.complete_time}</i>
					{@if it.complete_feedback == 2}
						<span class="font-ok">出价成功</span>
					{@else if it.complete_feedback == 3}
	<span>{@if it.pause_reason == 201}非推广时段{@else if it.pause_reason == 202 || it.pause_reason == 311}计划暂停{@else if it.pause_reason == 203}计划达到预算{@else if it.pause_reason == 211}账户达到预算{@else if it.pause_reason == 301}单元暂停{@else if it.pause_reason == 401}关键词暂停{@else if it.pause_reason == 2}创意无效{@else if it.pause_reason == 1}竞价地域无效{@else if it.pause_reason == 111}账户预算不足{@else if it.pause_reason == 103}账户余额为零{@else if it.pause_reason == 104}账户未通过审核{@else if it.pause_reason == 106}账户审核中{@else if it.pause_reason == 107}账户被禁用{@else}关键词未投放<i class="bid-icon tip tooltips" data-trigger="hover" data-placement="top" data-original-title="请查看百度推广账号设置"></i>{@/if}</span>
					{@else if it.complete_feedback == 4}
						<span>客户端离线，竞价暂停</span>
					{@else if it.complete_feedback == 5}
						<span class="font-ok">首页未发现竞争对手</span>
					{@else if it.complete_feedback == 6}
						<span class="font-ok">首页无推广广告</span>
					{@else if it.complete_feedback == 7}
						<span >限价过低</span><i class="bid-icon tip tooltips" data-trigger="hover" data-placement="top" data-original-title="采用限价争取排名"></i>
					{@else if it.complete_feedback == 8}
						<span >竞价词未触发,还原出价<i class="bid-icon tip tooltips" data-trigger="hover" data-placement="top" data-original-title="相同时段和地域存在相同的关键词，百度推广将优先使用出价高的达标关键词，如果您开启了智能竞价，当前关键词出价将会自动调整为上一轮出价"></i></span>
					{@else if it.complete_feedback == 9}
						<span class="font-ok">调整排名，出价成功</span>
					{@/if}
				{@/if}
				</span>
			</td>
		    </tr>       
		{@/each}
	{@else}
		<tr><td colspan="13">暂无数据</td></tr>
	{@/if}
        </tbody>
    </table>
</script>
{/literal}
{/function}
{function tpl_modal_open_bid}
{literal}
<script type="text/template" id="openBidTpl">
    <div id="modalOpenBid" class="modal hide set-bid modal-bid" data-width="560">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h3>修改竞价设置</h3>
        </div>
        <div class="modal-body smartFilterForm set-body">
            <form id="open_bidform" action="" method="">
			<input type="hidden" name="keyword_ids" value="${keyword_ids}"/>
            <div class="rows margin-top-10">
				<label>期望排名：<span class="current_rank">{@if rank1 == ''}左1{@else}${rank1}{@/if}</span></label>
                <div id="bid_rate_type">
				<div class="choose-rank rows">
					<input type="hidden" name="rank" value="{@if rank == ''}11{@else}${rank}{@/if}"/>
					<dl class="l">
						<dt>左侧排名</dt>
						<dd>
							<ul>
								<li class="cursor {@if rank == 11}on{@/if}{@if rank == ''}on{@/if}" rank="11"><span>1</span><i><b></b></i></li>
								<li class="cursor {@if rank == 12}on{@/if}" rank="12"><span>2</span><i><b></b></i></li>
								<li class="cursor {@if rank == 13}on{@/if}" rank="13"><span>3</span><i><b></b></i></li>
								<li class="cursor {@if rank == 14}on{@/if}" rank="14"><span>4</span><i><b></b></i></li>
								<li class="cursor {@if rank == 15}on{@/if}" rank="15"><span>5</span><i><b></b></i></li>
								<li class="cursor {@if rank == 16}on{@/if}" rank="16"><span>6</span><i><b></b></i></li>
								<li class="cursor {@if rank == 17}on{@/if}" rank="17"><span>7</span><i><b></b></i></li>
								<li class="cursor {@if rank == 18}on{@/if}" rank="18"><span>8</span><i><b></b></i></li>
							</ul>
						</dd>
					</dl>
					<dl class="r">
						<dd>
							<ul>
								<li class="cursor {@if rank == 21}on{@/if}" rank="21"><span>1</span><i><b></b></i></li>
								<li class="cursor {@if rank == 22}on{@/if}" rank="22"><span>2</span><i><b></b></i></li>
								<li class="cursor {@if rank == 23}on{@/if}" rank="23"><span>3</span><i><b></b></i></li>
								<li class="cursor {@if rank == 24}on{@/if}" rank="24"><span>4</span><i><b></b></i></li>
								<li class="cursor {@if rank == 25}on{@/if}" rank="25"><span>5</span><i><b></b></i></li>
								<li class="cursor {@if rank == 26}on{@/if}" rank="26"><span>6</span><i><b></b></i></li>
								<li class="cursor {@if rank == 27}on{@/if}" rank="27"><span>7</span><i><b></b></i></li>
								<li class="cursor {@if rank == 28}on{@/if}" rank="28"><span>8</span><i><b></b></i></li>
							</ul>
						</dd>
						<dt>右侧排名</dt>
					</dl>
				</div>
                </div>
            </div>
            <div class="rows">
                <label class="title">最高出价</label>
                <div class="controls">
                    <input type="text" class="max-bid" value="{@if max_bid != '--'}${max_bid}{@/if}" />&nbsp;元
                </div>
            </div>
            <div class="rows">
                <label class="title">重点竞价地域</label>
                <div class="controls">
                    <select id="bidRegion">
		                {@each region as it}
                            <option value="${it.id}" {@if it.default}selected{@/if}>${it.name}</option>
                        {@/each}
                    </select>
					<select id="bidLevel2" class="hide">
					</select>
                </div>
            </div>
			<div class="rows">
				<label class="title">
				<input type="checkbox" name="snipe"{@if snipe == 1}checked{@/if} id="sinpie_switch" value="{@if snipe == 1}1{@else}0{@/if}" /> 锁定竞争对手
				</label>
				<div class="controls">
				<select name="snipe_strategy" {@if snipe == 0}disabled{@/if}>
					<option value="1" {@if snipe_strategy == 1}selected{@/if}>超越对手</option>
					<option value="2" {@if snipe_strategy == 2}selected{@/if}>跟随对手</option>
				</select>
				</div>
			</div>
			<div class="snipe-box {@if snipe == 0}hide{@/if}">
				<div class="rows">
					<label class="title">竞争对手网址</label>
					<div class="controls">
					<input type="text" name="snipe_domain" value="${domain}"/> <a href="#" id="setCheck" target="_blank">校验</a>
					</div>
				</div>
				<div class="rows">
					<label class="inline">当竞争对手不在首页时</label>
					<input type="radio" name="snipe_without_strategy"{@if snipe_without == 2 || snipe_without == 0} checked {@/if} value="2" /> 采用期望排名
					<input type="radio" name="snipe_without_strategy"{@if snipe_without == 1} checked {@/if} value="1" /> 与对手保持同步
				</div>
			</div>
        </form>
            <p class="err_tips hide"></p>
        </div>
        <!--end modal-body-->
        <div class="modal-footer">
            <button type="button" class="btn sure" id="btnOpenBid">保存设置</button>
            <button type="button" data-dismiss="modal" class="btn">取消</button>
        </div>
    </div>
</script>
{/literal}
{/function}
{function calc_bid}
{literal}
<script type="text/template" id="clacBidTpl">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h3>计算出价</h3>
        </div>
	<form method="get" action="/refbid/calc" id="calcForm">
        <div class="modal-body calcForm">
		<input type="hidden" name="user_id" value="${user_id}"/>
		<input type="hidden" name="keyword_ids" value="${keyword_ids}"/>
            <div class="control-group">
                <label class="control-label">目标排名</label>
                <div class="controls clearfix">
                    <div class="rate-type-left pull-left">
                        <label class="radio">
                            <input type="radio" name="ratetype" id="rateTypeLeftCalc" checked>左侧排名</label>
                        <label class="radio">
                            <input type="radio" name="ratetype" id="rateTypeRightCalc" />右侧排名</label>
                    </div>
                    <div class="rate-type-right pull-left margin-top-10">
                    <input type="hidden" name="target_rank" value="11"/>
                        <select id="rateLeftCalc">
                            <option value="11">左1</option>
                            <option value="12">左2</option>
                            <option value="13">左3</option>
                            <option value="14">左4</option>
                            <option value="15">左5</option>
                            <option value="16">左6</option>
                            <option value="17">左7</option>
                            <option value="18">左8</option>
                        </select>
                        <select id="rateRightCalc" class="hide">
                            <option value="21">右1</option>
                            <option value="22">右2</option>
                            <option value="23">右3</option>
                            <option value="24">右4</option>
                            <option value="25">右5</option>
                            <option value="26">右6</option>
                            <option value="27">右7</option>
                            <option value="28">右8</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="control-group margin-top-20">
                <label class="control-label">最低出价</label>
                <div class="controls">
                    <input type="text" name="min_bid" />&nbsp;元 <span id="minBidHint" class="err"></span>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">最高出价</label>
                <div class="controls">
                    <input type="text" name="max_bid" />&nbsp;元 <span id="maxBidHint" class="err"></span>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">竞价地域</label>
                <div class="controls">
                    <select id="calcRegion">
		                {@each area as it}
                            <option value="${it.id}" {@if it.default}selected{@/if}>${it.name}</option>
                        {@/each}
                    </select>
					<select id="calcLevel2" class="hide" name="bid_area">
					</select>
                </div>
            </div>
        </div>
        <!--end modal-body-->
        <div class="modal-footer">
            <button type="submit" class="btn green">计算当前出价</button>
            <button type="button" data-dismiss="modal" class="btn">取消</button>
			<span id="calc_hint" class="err"></span>
        </div>
	</form>
</script>
{/literal}
{/function}
{function monitor_set}
{literal}
<script type="text/template" id="monitorSetTpl">
    <div id="modalMonitor" class="modal hide set-bid" data-width="900">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h3>${keyword} -- 最近24小时排名变化</h3>
        </div>
        <div class="modal-body">
			<div class="monitor_chart" id="monitor_chart">
			</div>
		</div>
        <div class="modal-footer sendMsg">
		<p>由于部分时段百度推广显示结果不足8条，在此时段当关键词排名未达到显示条数时将无法获取排名信息。</p>
		{@if is_agent == 1}
			{@if access[2].access == 2}
				<form method="get" action="" id="msgForm"> 
				<input type="hidden" name="initrank" value="1${monitor_rank}"/>
				<input type="hidden" name="keyword_id" value="${keyword_id}"/>
				<input type="checkbox" name="check"{@if monitor_rank !== ''} checked {@/if} id="checkMsg"/> &nbsp;
				当排名低于
				<select name="rank" id="rankMsg">
					{@each rank as it}
					<option value="1${it}" {@if monitor_rank == it} selected {@/if}>${it}</option>	
					{@/each}
				</select>&nbsp;
				时发送短信通知我<span>（短信接收号码：${mobile}）</span>
				</form>
			{@else}
				<form method="get" action="" id="msgForm"> 
				<input type="hidden" name="initrank" value="1${monitor_rank}"/>
				<input type="hidden" name="keyword_id" value="${keyword_id}"/>
				<input type="checkbox" name="check"{@if monitor_rank !== ''} checked {@/if} id="checkMsg" disabled="true"/> &nbsp;
				当排名低于
				<select name="rank" id="rankMsg" disabled="true">
					{@each rank as it}
					<option value="1${it}" {@if monitor_rank == it} selected {@/if}>${it}</option>	
					{@/each}
				</select>&nbsp;
				时发送短信通知我<span>（短信接收号码：${mobile}）</span>
				</form>
			{@/if}
		{@else}
		<form method="get" action="" id="msgForm"> 
		<input type="hidden" name="initrank" value="1${monitor_rank}"/>
		<input type="hidden" name="keyword_id" value="${keyword_id}"/>
		<input type="checkbox" name="check"{@if monitor_rank !== ''} checked {@/if} id="checkMsg"/> &nbsp;
        当排名低于
		<select name="rank" id="rankMsg">
			{@each rank as it}
			<option value="1${it}" {@if monitor_rank == it} selected {@/if}>${it}</option>	
			{@/each}
		</select>&nbsp;
		时发送短信通知我<span>（短信接收号码：${mobile}）</span>
		</form>

		{@/if}
        </div>
    </div>
</script>
{/literal}
{/function}
{function tpl_newTag}
{literal}
	<script type="text/template" id="newTagTpl">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h3>新建分组</h3>
			</div>
			<form action="/autobid/add_tag" method="get" id="newTagForm">
			<input type="hidden" name="user_id" value="${user_id}"/>
			<input type="hidden" name="keyword_ids" value="${keyword_ids}"/>
			<div class="modal-body newTag">
				<input type="text" name="tag" value="" placeholder="输入分组..." />
				<span id="err_tag" class="err"></span>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn green">确定</button>
				<button type="button" data-dismiss="modal" class="btn">取消</button>
			</div>
			</form>
	</script>
{/literal}
{/function}
{function tpl_tagList}
{literal}
	<script type="text/template" id="tagListTpl">
		<ul>
			{@each data as it}
			{@if it.tag != ''}
			<li>
				<i class="pull-right hzicon delete cursor" type="deltag"></i>
				<a href="#" type="modifytag" tag_id="${it.id}">${it.tag}
			</a></li>
			{@/if}
			{@/each}
		</ul>
	</script>
	<script type="text/template" id="tagMenuTpl">
			<li class="on"><a name="" href="#">我的竞价词</a></li>
			{@each data as it}
			{@if it.tag != ''}
			<li><a href="#" name="${it.id}">${it.tag}</a><p></p></li>
			{@/if}
			{@/each}
	</script>
{/literal}
{/function}
{function tpl_whiteList}
{literal}
	<script type="text/template" id="whiteListtpl">
		{@if white_list.length > 0}
		<h6>竞价白名单</h6>
		<ul>
			{@each white_list as it}
			<li><i class="hzicon white-list-icon"></i>${it} <span class="pull-right cursor white" name="${it}" type="del">删除</span></li>
			{@/each}
		</ul>
		{@/if}
	</script>
{/literal}
{/function}
{function tpl_area}
{literal}
<script type="text/template" id="areaTpl">
<div class="ctrlregionregionBody">
    <div class="regionSelect">
	</div>
</div>
</script>
{/literal}
{/function}
{function tpl_competitorList}
{literal}
<script type="text/template" id="competitorListTpl">
    <table class="table margin-top-10">
        <thead>
            <tr>
                <th class="first"><input type="checkbox" name="all" value="" /></th>    
                <th>跟踪状态</th> 
                <th>关键词</th>
                <th>跟踪地域</th>
                <th>对手1</th>
                <th>当前排名</th>
                <th>对手2</th>
                <th>当前排名</th>
                <th>对手3</th>
                <th>当前排名</th>
				{@if plan_id == ''}
				<th {@if unit_id != ''} class="last" {@/if}>所属计划</th>
				{@/if}
				{@if unit_id == ''}
				<th class="last">所属单元</th>
				{@/if}
            </tr>
        </thead>
        <tbody>            
		{@if list.length > 0}
		{@each list as it,index}
			<tr class="{@if index%2 == 0}odd{@else}even{@/if}">
				<td>
					<input type="checkbox" name="ids" competitors="${it.domains}" {@if it.competitors.length >0}competitors_area="${it.competitors[0].track_area}" {@/if} competitor_status="${it.competitor_status}" value="${it.keyword_id}" />
				</td>
				<td>
					{@if it.competitor_status == 3}
						{@if is_agent == 1}
							{@if access[2].access < 2}
								<span class="bid-play">
									<span class="bid-status">跟踪暂停</span>
								</span>
							{@else}
								<span class="competitor-operate bid-play">
									<span class="bid-status">跟踪暂停</span>
									<span class="bid-btn"><i class="hzicon" type="competitor-switch" status="${it.competitor_status}"></i></span>
								</span>
							{@/if}
						{@else}
						<span class="competitor-operate bid-play">
							<span class="bid-status">跟踪暂停</span>
							<span class="bid-btn"><i class="hzicon" type="competitor-switch" status="${it.competitor_status}"></i></span>
						</span>
						{@/if}
					{@else if it.competitor_status == 2}
						{@if is_agent == 1}
							{@if access[2].access < 2}
								<span class="bid-pause">
									<span class="bid-status">已开启</span>
								</span>
							{@else}
								<span class="competitor-operate bid-pause">
									<span class="bid-status">已开启</span>
									<span class="bid-btn"><i class="hzicon" type="competitor-switch" status="${it.competitor_status}"></i></span>
								</span>
							{@/if}
						{@else}
							<span class="competitor-operate bid-pause">
								<span class="bid-status">已开启</span>
								<span class="bid-btn"><i class="hzicon" type="competitor-switch" status="${it.competitor_status}"></i></span>
							</span>
						{@/if}
					{@else}
					<span class="bid-status bid-status-no">
						<span class="bid-status">未开启</span>
					</span>
					{@/if}
				</td>
				<td>
					{@if it.tag !== '' && it.tag}
						<span class="tag-icon">${/^[a-z]/g.test(it.tag) ? it.tag.substr(0,2) : it.tag.substr(0,1)}</span>
					{@/if}
					${it.keyword}
				</td>
				<td>
					{@if it.competitors.length >0}
					${it.competitors[0].track_area_name}
					{@else}
					--
					{@/if}
				</td>
				{@each it.competitors as c}
				<td>${c.domain_name}</td>
				<td>
				{@if c.rank == '' || c.rank <0}
					--
				{@else}
					{@if c.rank <19}
						${c.rank.replace(/^(.)/,'左')}
					{@else}
						${c.rank.replace(/^(.)/,'右')}
					{@/if}
				{@/if}
				{@if c.rank != ''}
					<span class="monitor">
						<i class="hzicon rate" type="competitor_rank" id="${c.competitor_id}" keyword="${it.keyword}"></i>
					</span>
				{@/if}
				</td>
				{@/each}
				{@if it.competitors.length <3}
					{@if it.competitors.length == 2}
						<td>--</td>
						<td>--</td>
					{@else if it.competitors.length == 1}
						<td>--</td>
						<td>--</td>
						<td>--</td>
						<td>--</td>
					{@else}
						<td>--</td>
						<td>--</td>
						<td>--</td>
						<td>--</td>
						<td>--</td>
						<td>--</td>
					{@/if}
				{@/if}
				{@if plan_id == ''}
				<td>${it.plan_name}</td>
				{@/if}
				{@if unit_id == ''}
				<td>${it.unit_name}</td>
				{@/if}
			</tr>
		{@/each}
		{@else}
			<tr><td colspan="12">暂无数据</td></tr>
		{@/if}
		</tbody>
	</table>
</script>
{/literal}
{/function}
{function tpl_initUserData}
{literal}
<script id="initUserDatatpl" type="text/template">
<div id="updateLayer" class="modal hide" tabindex="-1" data-width="484" data-backdrop="static">
	<div class="modal-header">
		<h3>数据同步</h3>
	</div>
	<div class="modal-body" >
		<p class="margin-top-10" id="userDatahint">正在同步您的百度推广数据，请您稍后......</p>
		<div class="loading"></div>
	</div>
	<div class="modal-footer">
	</div>
</div>
</script>
{/literal}
{/function}
{function tpl_competitor_open}
{literal}
<script id="competitorOpenTpl" type="text/template">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h3>开启跟踪</h3>
    </div>
	<form action="" id="competitorForm">
	<input type="hidden" name="user_id" value="${user_id}"/>
	<input type="hidden" name="keyword_ids" value="${keywords}"/>
	<div class="modal-body white-modal">
		<div class="control-group">
			<label class="control-label">添加竞争对手(最多3个)</label>
			<div class="controls">
				<input type="text" class="m-wrap" name="url" maxlength="200"/>
				<a href="" id="competitorCheck" target="_blank" class="btn green">校验</a>
				<input type="button" class="btn green" id="add_competitor" value="添加" />
			</div>
			<span id="err_competitor" class="err">${tips}</span>
		</div>
		<div class="control-group white-list">
			<ul id="competitor_list">
			{@each domains as it}
			<li><i class="hzicon white-list-icon"></i>${it}<span class="pull-right cursor" name="${it}">删除</span></li>
			{@/each}
			</ul>
		</div>
		<div class="control-group">
			<label class="control-label">跟踪地域：</label>	
			<div class="controls set-bid">
				<select id="competitor_area">
					{@each area as it}
						<option value="${it.id}" {@if it.default}selected{@/if}>${it.name}</option>
					{@/each}
				</select>
				<select id="competitor_area2" name="area" class="hide"></select>
			</div>
		</div>
		<input type="hidden" name="domains" value="${domain}"/>
		
	</div>
    <div class="modal-footer">
        <button type="submit" class="btn green">保存</button>
        <button type="button" data-dismiss="modal" class="btn">取消</button>
		<span id="compeittor_tips" class="err"></span>
    </div>
	</form>
</script>
{/literal}
{/function}
{function competitor_rank_tpl}
{literal}
<script id="competitor_rank_tpl" type="text/template">
    <div id="competitor_rank" class="modal hide set-bid" data-width="900">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h3>${keyword} -- 最近24小时排名变化</h3>
        </div>
        <div class="modal-body">
			<div class="monitor_chart" id="competitor_chart">
			</div>
		</div>
        <div class="modal-footer sendMsg">
			<p>由于部分时段百度推广显示结果不足8条，在此时段当关键词排名未达到显示条数时将无法获取排名信息。</p>
        </div>
    </div>
</script>
{/literal}
{/function}
{function bid_24_tpl}
{literal}
<script id="bid_24_tpl" type="text/template">
<div id="bid_24_modal" class="modal hide" data-width="558" data-height="344">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h3>24小时竞价</h3>
    </div>
	<div class="modal-body">
		<img src="/static/img/sem/24.jpg"/>
	</div>
	<div class="modal-footer">
		<p class="margin-bottom-10" style="color:#454c5b">如您需要停用智投易服务，请先关闭“24小时智能竞价”来停止智投易服务为您的关键词竞价。</p>
        <button type="button" class="btn sure" id="set_24">开启24小时竞价</button>
        <button type="button" data-dismiss="modal" class="btn">暂不开启</button>
		<span id="tips_24" class="err"></span>
	</div>
</div>
</script>
{/literal}
{/function}
{function tpl_photo}
{literal}
<script id="photo_tpl" type="text/template">
    <div class="modal-header photo-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h3>${keyword_name} {@if time}（${time}）{@/if}  当前排名：<span>${rank}</span> &nbsp;&nbsp;目标排名：<span>${target_rank}</span></h3>
    </div>
	<div class="modal-body photo-body">
	<iframe frameborder="0" width="600" height="300" scrolling="auto" id="iframe">
	</iframe>

	<div class="iframe-modal" id="iframe-modal"></div>
	</div>
</script>
{/literal}
{/function}
{function tpl_add_bidKeyword}
{literal}
<script id="addKeywordTpl" type="text/template">
<div id="addKeywordModal" class="modal modal-bid hide" data-backdrop="static" data-width="560" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h3>添加竞价词</h3>
    </div>
	<form id="add_keywordForm">
	<div class="modal-body">
		<div class="err_tips hide" id="add_err_tip"></div>
		<div class="my_keywords">
			<p class="step"><span>1</span>我要竞价的词</p>
			<div class="add-keyword-list">
				<input type="button" class="b-btn" id="add_keyword_btn" value="+ 点击添加" />
				<div class="choosed-box hide">
					<div class="hd"><span id="all_clear" class="all-clear">清空</span><input type="button" class="btn" id="continue_add" value="继续添加" />计划：<span class="plan_name"></span> (<span class="count"></span>) </div>
					<ul id="choosed_keyword_list">
					</ul>
				</div>
			</div>
		</div>
		<div class="step"><span>2</span>为我要竞争的词设置竞价目标</div>
		<div class="set-body step2-box">
			<div class="mask-layer" id="mask-layer"></div>
			<div class="rows">
				期望排名:<span class="current_rank">左1</span>
				<div class="choose-rank">
					<input type="hidden" name="rank" value="11"/>
					<dl class="l">
						<dt>左侧排名</dt>
						<dd>
							<ul>
								<li class="cursor on" rank="11"><span>1</span><i><b></b></i></li>
								<li class="cursor" rank="12"><span>2</span><i><b></b></i></li>
								<li class="cursor" rank="13"><span>3</span><i><b></b></i></li>
								<li class="cursor" rank="14"><span>4</span><i><b></b></i></li>
								<li class="cursor" rank="15"><span>5</span><i><b></b></i></li>
								<li class="cursor" rank="16"><span>6</span><i><b></b></i></li>
								<li class="cursor" rank="17"><span>7</span><i><b></b></i></li>
								<li class="cursor" rank="18"><span>8</span><i><b></b></i></li>
							</ul>
						</dd>
					</dl>
					<dl class="r">
						<dd>
							<ul>
								<li class="cursor" rank="21"><span>1</span><i><b></b></i></li>
								<li class="cursor" rank="22"><span>2</span><i><b></b></i></li>
								<li class="cursor" rank="23"><span>3</span><i><b></b></i></li>
								<li class="cursor" rank="24"><span>4</span><i><b></b></i></li>
								<li class="cursor" rank="25"><span>5</span><i><b></b></i></li>
								<li class="cursor" rank="26"><span>6</span><i><b></b></i></li>
								<li class="cursor" rank="27"><span>7</span><i><b></b></i></li>
								<li class="cursor" rank="28"><span>8</span><i><b></b></i></li>
							</ul>
						</dd>
						<dt>右侧排名</dt>
					</dl>
				</div>
			</div>
			<div id="set-bidparam">
			<div class="rows">
				<label class="title">最高出价</label>
				<div class="controls"><input type="text" class="s-text" name="max_price" value=""/></div>
			</div>
			<div class="rows">
				<label class="title">重点竞价地域</label>
				<div class="controls">
					<select id="regionLevel1">
					</select>
					<select id="regionLevel2" name="bid_area" class="hide"></select>
				</div>
			</div>
			<div class="rows">
				<label class="title">
				<input type="checkbox" name="snipe" class="b-text" id="sinpie_switchopen" value="0" />锁定竞争对手
				</label>
				<div class="controls">
				<select name="snipe_strategy" disabled>
					<option value="1">超越对手</option>
					<option value="2">跟随对手</option>
				</select>
				</div>
			</div>
				<div class="snipe-box hide">
					<div class="rows">
						<label class="title">竞争对手网址：</label>
						<div class="controls">
						<input type="text" name="snipe_domain"/> <a id="openCheck" href="#" target="_blank">校验</a>
						</div>
					</div>
					<div class="rows">
						<label class="inline">当竞争对手不在首页时：</label>
						<input type="radio" name="snipe_without_strategy" checked value="2" /> 采用期望排名
						<input type="radio" name="snipe_without_strategy" value="1" /> 与对手保持同步
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
        <button type="submit" class="b-btn hide">开启竞价</button>
	</div>
	</form>
</div>
</script>
{/literal}
{/function}
{function tpl_chooseKeyword}
{literal}
<script id="chooseKeywordTpl" type="text/template">
<div id="chooseKeyword" class="modal modal-bid hide" data-backdrop="static" data-width="560">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h3>选择关键词</h3>
    </div>
	<div class="modal-body no-padding">
		<div class="keyword-count">
			<p class="font-hint">智投易当前可添加${keyword_limit}个竞价词。</p>
			<p>
				<span id="new_count" >当前还可选 <b class="count">${count}</b> 个</span><b id="count" class="hide">0</b>
			</p>
		</div>
		<div class="choose-bd">
		<div class="left-menu" id="menu">
			<ul class="level-plan">
				{@each level.plan as p}
				<li class="plan-li">
					<p count="${p.keyword_count}" new_count="${Number(p.keyword_count) - Number(p.bid_keyword_count)}" type="plan" id="${p.id}" class="cursor level-one" region="${p.region}">
					    <i class="hzicon folder-closed" type="switch"></i>${p.name}</p>
					<ul class="level-unit hide">
						{@each p.unit as u}
							<li class="cursor" id="${u.id}" new_count="${Number(u.keyword_count) - Number(u.bid_keyword_count)}"type="unit" count="${u.keyword_count}">${u.name}</li>
						{@/each}
					</ul>
				</li>
				{@/each}
			</ul>
		</div>
		<div class="keyword-body" id="keyword_bd">
			<div class="hd">
			    <div class="filter-layer search-keyword pull-right">
                    <form action="" method="" class="form-horizontal pull-left" id="keyword_searchForm">
                        <input class="search" name="keyword" type="text" placeholder="搜索">
						<input type="submit" class="search-btn pull-right" id="keyword_search" value="" />
                    </form>
                </div>
			    <span id="current_plan" class="bold"></span>
			</div>
			<div class="bd">
				<ul id="keyword_list"></ul>
				<div class="page hide btn" id="keyword_page" index="1">更多</div>
			</div>
			<div class="ft"><label class="inline" for="all"><input type="checkbox" name="all" disabled value=""/> 全选</label></div>
		</div>
		</div>
		<p class="err_tips hide"></p>
		<div id="confirmKeyword" class="confirmKeyword hide">
			<div class="bd">
				<p class="b">一次只能从同一个计划中添加关键词。</p>
				<p class="b">如需添加其他计划中的关键词，请分批次操作。</p>
				<p>是否切换至“<span class="plan-name"></span>”添加关键词</p>
			</div>
			<div class="ft">
				<button type="button" class="btn" id="cancel_btn">取消</button>
				<button type="button" class="btn sure" id="continue_btn">切换</button>
			</div>
		</div>
	</div>
	<div class="modal-footer">
        <button type="button" class="b-btn" id="add_choosedkeyword">确认添加</button>
	</div>
</div>
</script>
<script id="keywordsTpl" type="text/template">
	{@if list.length != 0}
		{@each list as it}
		<li {@if it.bid_status != 1}class="bid"{@/if}><label class="inline" for="${it.keyword_id}"><input type="checkbox" ${it.checked} id="${it.keyword_id}" name="single" bid_status="${it.bid_status}" value="${it.keyword_id}"/>${it.keyword}</label>{@if it.bid_status != 1}<span>已添加</span>{@/if}</li>
		{@/each}
	{@else if(page.total_page < 2)}
		<li>暂无关键词</li>
	{@/if}
</script>
<script id="choosed_keywordtpl" type="text/template">
	{@each choosed.keyword as it}
	<li><span>${it.keyword}<a type="del" bid_status="${it.bid_status}" keyword_id="${it.keyword_id}">X</a></span></li>
	{@/each}
</script>
{/literal}
{/function}
{function tpl_bidRegion}
{literal}
<script id="bidRegionTpl" type="text/template">
	{@each region as it}
		<option value="${it.id}" {@if it.default}selected{@/if}>${it.name}</option>
	{@/each}
</script>
{/literal}
{/function}
{function tpl_questionnaire}
{literal}
<script id="questionnaireTpl" type="text/template">
<div id="questionnaire_modal" class="modal modal-question modal-new hide" data-width="540" data-height="300">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h3>问卷调查</h3>
    </div>
	<div class="modal-body">
		<div class="question-hd">为了帮助我们持续提升智能竞价服务，邀请你做一份问卷调查：</div>
		<p class="question-desc">在设置广告目标排名时，你是否需要使用区间排名？</p>
		<img src="/static/img/sem/question_1.png"/>
	</div>
	<div class="modal-footer">
        <button type="button" class="gray-btn y" id="question_y">是</button>
        <button type="button" data-dismiss="modal" id="question_n" class="gray-btn y">否</button>
        <button type="button" data-dismiss="modal" class="gray-btn n hide">关闭</button>
	</div>
</div>
</script>
<script id="questionokTpl" type="text/template">
<div class="questionHint">
	<i class="bid-icon ok-smile"></i>
	<p>
		<span>感谢你的配合!</span><br/>
		我们将持续为你优化智能竞价服务。
	</p>
</div>
</script>
{/literal}
{/function}

{function smart_template}
	{bid_24_tpl}
	{tpl_questionnaire}
	{tpl_initUserData}
	{white_list}
	{tpl_modalHint}
	{tpl_smartBidList}
	{tpl_competitorList}
	{tpl_pager}
	{calc_bid}
    {tpl_modal_open_bid}
	{tpl_modal_handle}
	{modal}
	{monitor_set}
	{tpl_whiteList}
	{tpl_newTag}
	{tpl_tagList}
	{tpl_area}
	{tpl_competitor_open}
	{competitor_rank_tpl}
	{tpl_photo}
	{tpl_add_bidKeyword}
	{tpl_chooseKeyword}
	{tpl_bidRegion}
	{tpl_more100}
	{tpl_keepKeywords}
	{tpl_checkMore100}
{/function}
