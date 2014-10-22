{extends file="base.tpl"}
{block content}
<div class="wrapper-two">
{helpleft page="status"}
<!-- BEGIN PAGE CONTAINER-->
    <div class="main main-right">
		{help_service}
        <div class="help-bd">
			<h5 class="help-title">修改竞价词状态</h5>
			<div class="help-body">
				<div class="help-border">
					<p class="help-title-a first"><i class="bid-icon dot"></i>1. 竞价词状态分为4种：</p>
					<ul class="sub-list">
						<li>① 等待竞价：首次添加或修改过设置竞价词，等待进入竞价队列。</li>
						<li>② 竞价中：竞价词已进入循环竞价状态。</li>
						<li>③ 关键词停投：表示你的关键词处于不可投放状态。</li>
						<li>④ 竞价暂停：暂停关键词竞价，在此状态下，系统将停止为对应关键词进行竞价。</li>
					</ul>
					<p>
						<img src="/static/img/sem/status1.jpg"/>
					</p>
					<p class="help-title-a"><i class="bid-icon dot"></i>2. 当你的关键词有如下情况时，将处于不可投放状态</p>
					<ul class="sub-list-2">
						<li>・关键词暂停</li>
						<li>・所属单元暂停</li>
						<li>・所属计划暂停</li>
						<li>・无有效创意关联</li>
						<li>・计划达到预算</li>
						<li>・账户达到预算</li>
						<li>・非推广时段</li>
						<li>・账户余额不足</li>
					</ul>
					<p class="help-title-a margin-top-20"><i class="bid-icon dot"></i>3. 修改单个竞价词状态：竞价状态列中的“开启/暂停”按钮，点击即可切换对应关键词的竞价状态。</p>
					<p class="help-title-a"><i class="bid-icon ok2"></i>4. 批量修改竞价词状态：从列表中勾选要进行设置的竞价词，然后点击页面顶部的“暂停竞价”、“启动竞价”来调整竞价状态。</p>
				</div>
					<p>
						<img src="/static/img/sem/status2.jpg"/>
					</p>
			</div>
    </div>
</div>
</div>
<!-- END PAGE CONTAINER-->
{/block}
{block customjs append}
{literal}
<script type="text/javascript">
$(function(){
/*
    var nav = $('#help_tree'),isSet = true;
    nav.click(function(e){
		var elem = e.target;

		if(elem.getAttribute('type')) return;
		e.preventDefault();
        var elem = e.target,href = elem.href;
        if(elem.nodeName != 'A') return;
        $(this).find('a').removeClass('on');
        $(elem).addClass('on');

		if(/box_c/g.test(href)){
			$(window).scrollTop(1280);
		}else if(/box_b/g.test(href)){
			$(window).scrollTop(371);
		}else if(/box_d/g.test(href)){
			$(window).scrollTop(1965);
		}else if(/box_e/g.test(href)){
			$(window).scrollTop(2266);
		}else{
			$(window).scrollTop(0);
		}
    })

    $(window).scroll(function(e){
       var h = $(this).scrollTop(); 
        if(h > 88){
            nav.addClass('help-nav');
        }else{
            nav.removeClass('help-nav');
        }
    })
*/
})
</script>
{/literal}
{/block}
