{extends file="base.tpl"}
{block login_body}login-body{/block}
{block content}
<div class="wrapper-two boxshadow-bottom">
<div class="mymain clearfix">
<div class="customer-main">
<h4 class="title">客服授权</h4>
<form class="form-horizontal customer-form" action="/hzuser/modify_access" id="customer_form">
<div id="customer_box">
</div>
    <div class="control-group">
        <label class="control-label">&nbsp;</label>
        <div class="controls">
			<input type="submit" class="btn bg28be7a" value="确定"/>
            <p class="err-tips hide"></p>
        </div>
    </div>
</form>
<div id="service_box" class="service-box2"></div>
</div>
</div>
</div>
{tpl_service}
{/block}
{block customjs append}
<script type="text/javascript" src="/static/scripts/login.js?v={$version}"></script>
{literal}
<script type="text/javascript">
$(function(){
	showService($('#service_box'));
	sem.customer_access(function(data){
			$('#customer_box').html(juicer($('#customer_tpl').html(),data));
	})
	$form = $('#customer_form');
	$form.click(function(e){
		var elem = e.target;
		
		if(elem.nodeName != 'INPUT' || elem.type != 'checkbox') return;
			if(elem.checked){
				elem.value = 2;	
			}else{
				elem.value = 1;
			}
	})

	$form.submit(function(e){
		e.preventDefault();
		var a = 'data=[';
		$.each($form.find('input[name="check"]'),function(key,val){
			a +='{"module_id":'+ val.getAttribute("d") +',"access":'+val.value+'},'
		})
		a = a.replace(/,$/,'');
		a +=']';

		$.ajax({
			type:'get',
			url:this.action,
			dataType:'json',
			data:a,
			success:function(data){
				if(data.status == 'success'){
					location.reload();	
				}else{
					var code = data.error_code;
					if(code < 7){
						sem.errTip(code)
					}else{
						switch(code){
							case 7: sem.modalAlert('传入数据有误');break;
							case 8: sem.modalAlert('更新失败');break;
						}
					
					}
				}
			}
		})
			
	})
})
</script>
<script type="text/template" id="customer_tpl">
	
	{@each data as it}
    <div class="control-group">
        <div class="controls">
            <input class="m-wrap" type="checkbox" name="check" d="${it.id}" {@if it.access == 2}checked{@/if} value="${it.access}"/>
			允许我的客服使用<span>${it.name}</span>
        </div>
    </div>
	{@/each}
</script>
{/literal}
{/block}
