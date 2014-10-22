{extends file="base.tpl"}
{block content}
<div class="wrapper-two">
{helpleft page="feedback"}
<!-- BEGIN PAGE CONTAINER-->
    <div class="main main-right">
		{help_service}
        <div class="help-bd">
			<h5 class="help-title">竞价反馈</h5>
			<div class="help-body">
				<div class="help-border">
				<p class="help-title-a first"><i class="bid-icon dot"></i>1. 如图所示，当你的竞价词完成一轮竞价后，列表最后一列将实时显示竞价完成时间和竞价详情。</p>
					<p>
						<img src="/static/img/sem/feedback1.jpg"/>
					</p>
					<p class="help-title-a"><i class="bid-icon ok2"></i>2. 竞价详情内容说明：</p>
				</div>
					<table class="help-table">
						<thead>
							<tr><th width="300">状态</th><th>说明</th></tr>
						</thead>
						<tbody>
							<tr>
								<td>出价成功</td>
								<td>竞价成功，到达预期排名</td>
							</tr>
							<tr>
								<td>客户端离线，竞价暂停</td>
								<td>客户退出</td>
							</tr>
							<tr>
								<td>首页未发现竞争对手</td>
								<td>竞争对手不在首页</td>
							</tr>
							<tr>
								<td>首页无推广广告</td>
								<td>百度搜索结果首页没有任何广告展示</td>
							</tr>
							<tr>
								<td>限价过低</td>
								<td>达到最高出价还未达到预期排名系统采用最高出价争取排名</td>
							</tr>
							<tr>
								<td>竞价词未触发，还原出价</td>
								<td>竞价词命中了其他竞价词，还原上一轮出价</td>
							</tr>
							<tr>
								<td>调整排名，出价成功</td>
								<td>目标广告位无广告，系统自动调整至合适排名</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
    </div>
</div>
</div>
<!-- END PAGE CONTAINER-->
{/block}
