{extends file="base.tpl"}
{block content}
<div class="wrapper-two">
{helpleft page="modify"}
<!-- BEGIN PAGE CONTAINER-->
    <div class="main main-right">
		{help_service}
        <div class="help-bd">
			<h5 class="help-title">修改竞价词设置</h5>
			<div class="help-body">
				<div class="help-border">
					<p class="help-title-a first"><i class="bid-icon dot"></i>1. 修改单个竞价词设置：鼠标移入某个竞价词名称上，竞价词右侧会出现齿轮图标，点击竞价词即可进行竞价设置</p>
					<p>
						<img src="/static/img/sem/modify1.jpg"/>
					</p>
					<p class="help-title-a"><i class="bid-icon dot"></i>2. 批量修改竞价设置：从列表中勾选要进行设置的竞价词，然后点击页面顶部的“修改竞价词设置”打开竞价设置面板。</p>
					<p>
						<img src="/static/img/sem/modify2.jpg"/>
					</p>
					<p class="help-title-a"><i class="bid-icon ok2"></i>3. 竞价设置内容如下：</p>
				</div>
					<p>
						<img src="/static/img/sem/modify3.jpg"/>
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
