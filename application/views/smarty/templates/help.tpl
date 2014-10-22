{extends file="base.tpl"}
{block content}
<div class="wrapper-two">
{helpleft page="add"}
<!-- BEGIN PAGE CONTAINER-->
    <div class="main main-right">
		{help_service}
        <div class="help-bd">
			<h5 class="help-title">添加竞价词</h5>
			<div class="help-body">
				<div class="help-border">
					<dl>
						<dt class="help-title-a first"><i class="bid-icon dot"></i>1. 点击左上角“<b>添加竞价词</b>”按钮。</dt>
						<dd>
							<img src="/static/img/sem/add1.jpg"/>
						</dd>
					</dl>
					<dl>
						<dt class="help-title-a"><i class="bid-icon dot"></i>2. 在弹出的窗口中，点击中间的“<b>点击添加</b>”按钮</dt>
						<dd>
							<img src="/static/img/sem/add2.jpg"/>
						</dd>
					</dl>
					<dl>
						<dt class="help-title-a"><i class="bid-icon dot"></i>3. 在弹出的窗口中，会根据你的推广账号结构显示关键词，请勾选要进行竞价的关键词（最多100个），然后点击“<b>确认添加</b>”按钮。</dt>
						<p style="margin-left:37px;margin-top:-12px;">提示：由于各个推广计划的地域不同，如果你需要添加多个计划关键词，请分次操作。</p>
						<dd>
							<img src="/static/img/sem/add3.jpg"/>
						</dd>
					</dl>
					<dl>
						<dt class="help-title-a"><i class="bid-icon dot"></i>4. 添加完关键词后，接下来需要设置相应的竞价设置</dt>
						<dd>
							<img src="/static/img/sem/add4.jpg"/>
						</dd>
					</dl>
					<dl>
						<dt class="help-title-a"><i class="bid-icon ok2"></i>5. 点击“<b>开启竞价</b>”后，即可完成竞价词的添加并开始竞价。</dt>
					</dl>
				</div>
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
