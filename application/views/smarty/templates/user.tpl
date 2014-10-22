{extends file="base.tpl"}
{block page_width}page-big{/block}
{block content}
<!-- BEGIN CONTAINER -->
<div class="page-content">
	<!-- BEGIN PAGE CONTAINER-->
<div class="wrapper-left boxshadow-bottom">
    <div class="cleft transition">
        <div class="portlet blue">
            <div class="portlet-title">
                <div class="caption">我的推广账户</div>
                <a href="#" class="title-btn" id="btnAddAccount" title="添加新账户"><i class="hzicon add"> </i></a>
            </div>
            <div class="portlet-body">
                <div id="levelTree" class="transition"></div>
            </div><!--end portlet-body-->
        </div><!--end portlet-->
		<i class="hzicon tree-width cursor" id="treeSwitch"></i>
    </div>
    <div class="main transition">
        <div class="clearfix"></div>
        <div class="account-error">
            <div class="alert account-error-1 hide clearfix margin-top-20">
                <span class="tipicon"><i class="hzicon notice-big"></i></span>
                百度API服务器响应异常，暂时无法进行正常操作。<br/>我们正在不断尝试连接百度API服务器，待服务恢复后将会第一时间完成对接，如有其他疑问请联系您的客服。<br />当前数据更新时间：<span class="account-last-update"></span>
            </div>
            <div class="alert account-error-2 hide clearfix margin-top-20">
                <span class="tipicon"><i class="hzicon notice-big"></i></span>
                <span class="nickname">海智智投易</span> 您好！由于您的<span class="account-name">SEM</span>推广账户密码错误，智投易系统获取账户数据失败，请重新<a data-toggle="modal" href="#accountReLogin">输入账户密码</a>更新账户数据。<br />当前数据更新时间：<span class="account-last-update"></span>
            </div>
            <div class="alert account-error-3 hide clearfix margin-top-20">
                <span class="tipicon"><i class="hzicon notice-big"></i></span>
                <span style="display:inline-block;margin-top:8px;"><span class="nickname">海智智投易</span> <span class="balance-tips">您好！您当前账户金额预计可消费<span class="consume-days">0</span>天，账户余额不足将会影响正常竞价和推广，请及时充值！</span></span>
            </div>
        </div><!--end account-error-->
            <div id="port-plan" class="g-change manage-stat margin-top-20 br3 clearfix  hide"></div>
            <div id="port-unit" class="g-change manage-stat margin-top-20 br3 clearfix  hide"></div>
            <div class="g-title clearfix">
                <div class="caption pull-left">推广概况</div>
                <ul class="btn-groups pull-right margin-top-20" id="chosseDate">
                  <li class="boxshadow-bottom on">
                    <a href="#" type="yesterday">昨天</a>
                  </li>
                  <li class="boxshadow-bottom">
                    <a href="#" type="last7">最近7天</a>
                  </li>
                  <li class="boxshadow-bottom"><a href="#" type="last30">最近30天</a></li>
                  <li class="last boxshadow-bottom"><a id="promoteDate" class="date-range">自定义时间段</a></li>
                </ul>
                <!--div id="promoteDate" class="pull-right date-range margin-top-20">
                    <i class="hzicon calendar"></i>
                    <span>选择时间</span>
                    <i class="hzicon angle-down"></i>
                </div-->
            </div><!--end portlet-title-->
            <div class="stat-box bgfff boxshadow-bottom br3">
                <div class="clearfix" id="summary"></div><!--end clearfix-->
                <div class="chart-box">
                    <div class="chart-all">
                        <div class="clearfix" id="chart_select"></div>
                        <div id="spreadChart"></div><!--推广概况图表-->
                    </div><!--end chart-all-->
                </div>
                <span class="pull-right hide-chart"><i class="hzicon cstat"></i></span>
            </div>
            <div class="tabbable boxshadow-bottom br3">
                <ul class="tabs g" id="nav_tabs">
                   <li class="active"><a href="#portlet_tab1">计划</a></li>
                   <li><a href="#portlet_tab2">单元</a></li>
                   <li><a href="#portlet_tab3">关键词</a></li>
                   <li><a href="#portlet_tab4">创意</a></li>
                </ul>
                <div class="tab-content g-change" id="tab_content">
                    {userplan}
                    {userunit}
                    {userkeyword}
                    {usercreative}
                    <div id="inlineEdit" class="input-append hide">
                        <input type="text" maxlength="30" id="inlineEditName">
                        <a href="javascript:void(0)" class="btn" id="saveEditName">保存</a>
                        <a href="javascript:void(0)" class="btn" id="cancelEditName">取消</a>
                    </div>
                </div><!--end tab-content-->
            </div><!--end tabbable-->
    </div><!--end main-->
</div><!--end wrapper-left-->
<div class="cright bgfff">
	{userinfo}
</div>
<!-- END PAGE -->
</div>
{modal_plan_negkeyword}
{modal_accounterror}
{/block}
{block customjs append}
<script type="text/javascript" src="/static/scripts/manage.js?v={$version}"></script>
{add_template}
{/block}
