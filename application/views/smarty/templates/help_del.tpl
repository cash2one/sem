{extends file="base.tpl"}
{block content}
<div class="wrapper-two">
{helpleft page="del"}
<!-- BEGIN PAGE CONTAINER-->
    <div class="main main-right">
		{help_service}
        <div class="help-bd">
			<h5 class="help-title">删除竞价词</h5>
			<div class="help-body">
				<div class="help-border">
					<p class="help-title-a first"><i class="bid-icon dot"></i>1. 从列表中勾选要删除的竞价词。</p>
					<p class="help-title-a"><i class="bid-icon ok2"></i>2. 点击“删除竞价词”按钮，确认即可删除所选的竞价词（删除操作不会影响您百度推广账户中的关键词）。</p>
				</div>
					<p>
						<img src="/static/img/sem/del1.jpg"/>
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
