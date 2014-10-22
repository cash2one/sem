var buy = {
		applying:function(initInfo){
			var tpl = $('#apply').html(),me = this;

			$('#buy_box').html(juicer(tpl,initInfo))

			var chartdata = [['已添加',parseInt(initInfo.chartdata['已添加'])],['剩余可用',initInfo.chartdata['剩余可用']]];

			$('#chart').highcharts({
				chart:{type:'pie'},
				title:{
					text:parseInt(initInfo.chartdata.percent*100)+'%',
					align:'center',
					style:{"font-size":'20px',"color":'#ff9900',"font-weight":'bold'},
					verticalAlign:'middle',
					y:7
				},
				credits:{enabled:false},
				colors:['#ff9900','#f3f3f3'],
				plotOptions:{pie:{
					center:['50%','50%'],
					dataLabels:{
						enabled:true,
						formatter:function(){
							return this.point.name +'<br/>'+initInfo.chartdata[this.point.name];
						}
					},
					enableMouseTracking:false,
					size:160
				}},
				tooltip:{
					enabled:false
				},
				series:[
					{
						type:'pie',
						innerSize:'55%',
						data:chartdata
					}
				]

			})

			$('#continue').click(function(){
				me.buypkg('continue');				
				return false;
			})

			$('#up').click(function(){
				me.buypkg('up');				
			})

				 
		},
		buypkg:function(type){
			var tpl = $('#buy_pkg').html();
			$.ajax({
				type:'get',
				url:'/hzuser/pkg',
				data:{user_id:sem.userId},
				dataType:'json',
				success:function(data){
					if(data.status == 'success'){
						var info = {};
						info = data;
						info.user_id = sem.userId;
						info.type = type;
						if(type == 'first'){
							info.len = info.all_pkg_info.length
							$.each(info.all_pkg_info,function(key,val){
								if(val.recommend == 1){
									info.recommend = val;
									info.num = key+1;
									return false;
										
								}
							})
						}else{
							info.len = info.upgrade_pkg_info.length
							$.each(info.upgrade_pkg_info,function(key,val){
								if(val.recommend == 1){
									info.recommend = val;
									info.num = key+1;
									return false;
										
								}
							})
						
						}
						if(type == 'continue'){
							info.len = 1;
							info.num = 1;
						}
						$('#buy_box').html(juicer(tpl,info));

						var pkg_list = $('#pkg_list'),$form = $('#buyForm'),form = $form[0];

						$('#prev').click(function(){
							var l = parseInt(pkg_list[0].style.left);	
							if(l == 0){
								pkg_list.css('left','-240px')
							}else{
								pkg_list.css('left','0px')
							}
						})
						
						$('#next').click(function(){
							var l = parseInt(pkg_list[0].style.left);	
							if(l == 0){
								pkg_list.css('left','-240px')
							}else{
								pkg_list.css('left','0px')
							}
						})

						pkg_list.find('li').click(function(e){
							var pkg_info = {},s;

							s = this.getAttribute('pkginfo').split(',');

							pkg_info.id = s[0];
							pkg_info.bid_keyword_num = s[1];
							pkg_info.money = s[2];
							if(type == 'up'){
								pkg_info.total = s[3]
								pkg_info.paid_total = s[4]
								pkg_info.expiration_date = s[5]
							}

							pkg_list.find('li').removeClass('on');
							$(this).addClass('on');
							form.apply_package.value = pkg_info.id;
							console.log(pkg_info);
							$('#keyword_num').html(pkg_info.bid_keyword_num);
							$('#expiration_date').html(pkg_info.expiration_date);
							$('#money').html(Number(pkg_info.paid_total)+Number(pkg_info.total));
							$('#regund').html(pkg_info.paid_total);
							$('#total').html(pkg_info.total);
						})

						var $tip = $('.err_tips');

						//申请购买
						$form.submit(function(e){
							e.preventDefault();
							$.ajax({
								type:'get',
								url:'/vas/apply',
								data:$form.serialize(),
								dataType:'json',
								success:function(data){
									if(data.status == 'success'){
										var o = {};
										if(type == 'first'){
											o.text = '套餐申请成功！';
										}else if(type == 'up'){
											o.text = '套餐升级申请成功！';	
										}else{
											o.text = '套餐续费申请成功！';	
										}
										$('#buy_box').html(juicer($('#ok_hint').html(),o));
										$('#cat_result').click(function(){
											location.reload();
											return false;		
										})
									
									}else{
										var code = data.error_code;	
										if(code == 1){
											$tip.html('登录超时，请重新登录')
										}else{
											$tip.html('出错啦').show();
										}
									}
								}
							})
								
						})
						$('#back').click(function(){
							location.reload();			
						})

					}else{
						$('#buy_box').html('出错了')
					}
				
				}
			})
					   
		},
		firstBuy:function(){
			var me = this;
			me.buypkg('first');
		}
		

};
$(function(){
	var apply_status = sem.hzuserInfoData.apply_status,
		initInfo = sem.hzuserInfoData,
		n = initInfo.autobid_keyword_amount/initInfo.keyword_limit*100;
		m = 100-n;


		initInfo.chartdata = {percent:initInfo.autobid_keyword_amount/initInfo.keyword_limit,'已添加':initInfo.autobid_keyword_amount,'剩余可用':initInfo.keyword_limit-initInfo.autobid_keyword_amount};
		//initInfo.chartdata = {percent:0.80,'已添加':initInfo.autobid_keyword_amount,'剩余可用':initInfo.keyword_limit-initInfo.autobid_keyword_amount};

		if('is_max' in initInfo.opened_package_info){
			initInfo.is_max = 1;
		}else{
			initInfo.is_max = 0;
		}
	
		switch(apply_status){
			case 0://第一次申请
				buy.firstBuy();
			break;
			case 1: //有正在申请的套餐,没有已开通的套餐
				buy.applying(initInfo);	
			break;
			case 2: //没有正在申请的套餐，有已开通的套餐
				buy.applying(initInfo);	
			break;
			case 3://有正在开通的套餐，且有正在申请的套餐
				buy.applying(initInfo);	
			break;
			default:
				buy.firstBuy();

		}
})
