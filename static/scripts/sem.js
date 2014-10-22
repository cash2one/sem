function commonInit() {
    //账户异常重新输入密码
    $("#btnReLogin").on("click", function () {
        var password = $.trim($("#accountReLogin").find(".password").val()),
            $tip = $("#accountReLogin").find(".alert");
        if (password == "") {
            $tip.html("密码不能为空").show();
            return false;
        }
        $.ajax({
            type: 'post',
            url: '/user/reset_pwd',
            dataType: 'json',
            data: {"password": password, "user_id": hzUserInfoData.default_bind_user},
            success: function (data) {
                if (data.status == "success") {
                    location.reload();
                } else {
                    var errorCode = data.error_code, errorMessage = "";
                    switch (errorCode) {
                        case '1':
                            location.href = '/';
                            break;
                        case "8":
                            errorMessage = "密码错误";
                            break;
                        case "9":
                            errorMessage = "不需要重新登录";
                            break;
                        case "8501":
                            errorMessage = "当前账号没有足够配额";
                            break;
                        default :
                            errorMessage = "未知错误";
                            break;
                    }
                    $tip.html(errorMessage).show();
                }
            }
        });
    });
}



/*
 * 初始账户信息，从其他SEM下载数据到智搜易
 */
function updateAccountData() {
    var url = "/user/init";
    //$(".open-layer").click();
	if ($("#updateLayer").length == 0) {
		var initTpl = $('#initUserDatatpl').html();

		$("body").append(initTpl);
	}
	$updateLayer = $('#updateLayer');
	$updateLayer.modal();

    $.ajax({
        type: 'get',
        url: url,
        async: false,
        data: {"user_id": hzUserInfoData.default_bind_user},
        dataType: 'json',
        success: function (data) {
            var $updateLayer = $("#updateLayer");
            $updateLayer.find(".modal-footer").show();
            if (data.status == "success") {
                sem.syncUserData();
            } else {
                var errorCode = data.error_code, errorMessage = "";
                switch (errorCode) {
                    case "7":
                        errorMessage = "USERID错误";
                        break;
                    case "8":
                        errorMessage = "10分钟内已经请求过";
                        break;
                    case "10":
                        errorMessage = "sem api请求错误";
                        break;
                    case "9":
                        errorMessage = "正在初始化";
                        break;
                    case "12":
                        errorMessage = "已经初始化过了";
                        break;
                    case "11":
                        errorMessage = "未知错误11";
                        break;
                    default:
                        errorMessage = "其它错误";
                        break;
                }
                $updateLayer.find(".modal-body").html('<div class="ok-layer"><div class="modal-left"><i class="bid-icon notice"></i></div><div class="modal-raight"><p class="margin-top-10">'+errorMessage+'</p></div></div>');
            }
        }
    });
}

/*
 * 计划|单元|关键词|创意|标签(删除) 部分批量处理
 */
function modifyPlan(type, reqData, callback) {
    var datatype = type, urltype = type, tpltype = type;
    var data = $.extend({"user_id": hzUserInfoData.default_bind_user}, reqData);
    var list = "list";
    switch (type) {
        case "plan":
            break;
        case "unit":
            break;
        case "keyword":
            ;
        case "creative":
            list = "feed";
            break;
        case "autobid":
            urltype = "keyword";
            datatype = "autobid";
            tpltype = "smartBid";
            break;
    }
    $.ajax({
        type: 'get',
        url: "/" + urltype + "/modify",
        dataType: 'json',
        data: data,
        cache: false,
        success: function (data) {
            if (data.status == "success") {
                callback();
                $(".modal").modal("hide");
                //if(type == 'autobid') return;
                sem.getListData({url: '/' + datatype + '/' + list + '', container: datatype + '_list', tpl: tpltype + 'ListTpl'});
            } else {
                var errorCode = data.error_code, errorMessage = "";
                switch (errorCode) {
                    case '1':
                        location.href = '/';
                    case "7" :
                        errorMessage = "USERID错误";
                        break;
                    case "901262" :
                        errorMessage = "否定关键词不能包含特殊字符";
                        break;
                    case "901253" :
                        errorMessage = "每一个否定关键词长度不能超过40字符";
                        break;
                    default :
                        errorMessage = "未知原因";
                        break;
                }
                sem.modalAlert("修改失败:" + errorMessage);
            }
        }
    });
}


//修改计划预算，不包括右侧修改全局预算
var sem = {
	userInfoData:'',
    userId:'',
	userIndex:0,
	name:'',
	tag_id:'',
    planId:'',
	planIndex:0,
	planStatus:'',
    unitId:'',	
	unitIndex:0,
	unitStatus:'',
	refreshKeywords:'',
	currentKeyword:{},
	bidKeywordCount:0,
	choosed:{num:0,new_count:0,plan_id:'',unit_id:'',isall:{plan:'',unit:{}},keyword_id:'',del_keyword_id:'',keyword:{}},
	submitChoosed:{num:0,plan_id:'',unit_id:'',keyword_id:'',del_keyword_id:''},
	roundKeywords:{},
	getKeywordsRound:function(data){
		var me = this;
		$.each(data,function(index){
			me.roundKeywords[data[index].keyword_id] ={round:'',bid_status:'',pause_autobid:''}
			me.roundKeywords[data[index].keyword_id].round = data[index].round;		
			me.roundKeywords[data[index].keyword_id].bid_status = data[index].bid_status;		
			me.roundKeywords[data[index].keyword_id].pause_autobid = data[index].pause_autobid;		
		})
	},
	/*
	 * 	 * 获取账户层级树
	 *  */
	getuserlevel:function(type,callback){
		$.ajax({
			type:'get',
			url:'/user/level_tree',
			data:{"user_id":sem.userId,type:type},
			cache:false,
			dataType:'json',
			success:function(data){
				if(data.status=="success"){
					if(callback){
						callback(data.data)
					}
				}else{
					var errorCode = data.error_code,errorMessage = "";
					switch(errorCode){
						case "1":errorMessage="您还没有登录";break;
						case "2":errorMessage="账号不存在";break;
						case "3":errorMessage="账号已被停用";break;
						case "4":errorMessage="账号过期，请重新充值";break;
					}
					sem.modalAlert("无法获取到您的账户层级树，原因是："+errorMessage);
				}
			}
		});
	},
	addKeywordHandle:function(){
		if($('#addKeywordModal').length == 1){
			$('#addKeywordModal').remove();
		}
		$(document.body).append($('#addKeywordTpl').html());
		$('#addKeywordModal').modal();
		$('#add_keyword_btn').click(function(){
			if($('#chooseKeyword').length == 1){
				$('#chooseKeyword').remove();
			}
			sem.getuserlevel(0,function(data){
				$(document.body).append(juicer($('#chooseKeywordTpl').html(),{count:sem.keyword_limit-sem.bidKeywordCount,level:data[0],keyword_limit:sem.keyword_limit}));
				$('#chooseKeyword').modal();
				sem.levelHandle();
			})
		})
		//继续添加
		$('#continue_add').click(function(){
			$('#chooseKeyword').modal();
		})
		var $form = $('#add_keywordForm'),form = $form[0],
			$li = $('.choose-rank li'),rank='',
			$err = $('#add_err_tip');
		$('#all_clear').click(function(){
			$('#choosed_keyword_list').html('');	
			sem.clearAddKeyword('data');
			$('#keyword_bd').find('input').attr('checked',false);
			$('#count').html(0);
			$(this).parent().find('.count').html(0);
			$('#add_keyword_btn').show();
			$('.choosed-box').hide();
			$('#mask-layer').show();
			$form.find('button[type="submit"]').hide();
		})
		//删除
		$('#choosed_keyword_list').click(function(e){
			var elem = e.target;
			if(elem.nodeName != 'A') return;
			var keyword_id = elem.getAttribute('keyword_id');
				sem.submitChoosed.num -= 1;
				sem.choosed.num -= 1;
				if(elem.getAttribute('bid_status') == 1){
					sem.choosed.new_count -= 1;
				}
				if(sem.submitChoosed.keyword_id.indexOf(keyword_id) >= 0){
					sem.submitChoosed.keyword_id = sem.submitChoosed.keyword_id.replace(keyword_id+',','');
					sem.choosed.keyword_id = sem.choosed.keyword_id.replace(keyword_id+',','');
				}else{
					sem.submitChoosed.del_keyword_id += keyword_id + ',';
					sem.choosed.del_keyword_id += keyword_id + ',';
				}
				delete sem.choosed.keyword[keyword_id];
				$(elem).parents('li').remove();
				$('#keyword_list').find('#'+keyword_id).attr('checked',false);
				$('.choosed-box').find('.count').text(sem.submitChoosed.num);
				$('#count').text(sem.submitChoosed.num);
				$('#new_count').text(sem.keyword_limit - sem.bidKeywordCount - sem.choosed.new_count);
				if(sem.choosed.num <= 0){
					$('#add_keyword_btn').show();
					$('.choosed-box').hide();
					$('#mask-layer').show();
					$form.find('button[type="submit"]').hide();
				}
		})
		$('#addKeywordModal .close').click(function(){
			$('#addKeywordModal').modal('hide');
			sem.clearAddKeyword();
		})

		$('#sinpie_switchopen').click(function(){
			if(this.checked){
				this.value = 1;
				$('.snipe-box').show();
				form.snipe_strategy.disabled = false;
				$form.find('.add-keyword-list').addClass('hover');
			}else{
				this.value = 0;
				$('.snipe-box').hide();
				form.snipe_strategy.disabled = true;
				$form.find('.add-keyword-list').removeClass('hover');
			}
		})
		$('#openCheck').click(function(){
			var domain = form.snipe_domain.value;
			if(domain == ''){
				$err.html('请输入竞争对手网址').show();
				return false;	
			}
			domain = /^http:\/\//.test(domain) ? domain : 'http://'+domain;
			if(!RegTool.urlReg.test(domain)){
				$err.html('对手网址格式错误').show();
				return false;
			}
			this.href = 'http://www.baidu.com/#wd=' + domain;
		})
		//省市切换
		$('#regionLevel1').change(function(){
			if(this.value != ''){
				var op = '';
				$.each(sem.sumArea[this.value]['childs'],function(key,val){
					if(val.default){
						op += '<option value="'+val.bid_id+'" selected>'+val.name+'</option>';
					}else{
						op += '<option value="'+val.bid_id+'">'+val.name+'</option>';
					}
				})

				$('#regionLevel2').html(op);
				if(sem.sumArea[this.value].hide){
					$('#regionLevel2').hide();
				}else{
					$('#regionLevel2').show();
				}
			}
		})
		$li.click(function(){
			$li.removeClass('on');
			$(this).addClass('on');
			rank = this.getAttribute('rank');
			$('.current_rank').text(rank.replace(/^(.)/,function($1){
				if($1 == 1){
					return '左';
				}else{
					return '右';
				}
			}));
			form.rank.value = rank;
		})
		$form.submit(function(e){
			e.preventDefault();
			var price = form.max_price.value,
				target_rank = form.rank.value;

			if(sem.submitChoosed.num == 0){
				$err.html('请选择关键词').show();
				return;
			}
			if(target_rank == ''){
				$err.html('请选择目标排名').show();
				return;
			}
			if(price == ''){
				$err.html('请输入最高出价').show();
				return;
			}
			if(!form.bid_area.value){
				$err.html('请选择重点竞价地域').show();
				return;
			}
			if(!RegTool.MoneyReg.test(price)){
				$err.html('最高出价格式错误').show();
				return;
			}
			if(form.snipe.value == 1){
				if(form.snipe_domain.value == ''){
					$err.html('请输入对手网址').show();
					return;
				}
				var domain = /^http:\/\//.test(form.snipe_domain) ? form.snipe_domain.value : 'http://'+form.snipe_domain.value;
				if(!RegTool.urlReg.test(domain)){
					$err.html('对手网址格式错误').show();
					return;
				}
				var snipe_without_strategy = '';
				$form.find('input[name="snipe_without_strategy"]').each(function(){
					if(this.checked){
						snipe_without_strategy =  this.value;
					}
				})
			}
			$err.html('').hide();
			var requestData = {};
			requestData.user_id = sem.userId;
			requestData.plan_id = sem.submitChoosed.plan_id;
			requestData.unit_ids = sem.submitChoosed.unit_id.replace(/,?$/,'');
			requestData.keyword_ids = sem.submitChoosed.keyword_id.replace(/,?$/,'');
			requestData.rest_ids = sem.submitChoosed.del_keyword_id ? sem.submitChoosed.del_keyword_id.replace(/,?$/,'') : '';
			requestData.target_rank = target_rank;
			requestData.bid_area =form.bid_area.value;
			requestData.max_bid = price;
			requestData.tag_id = sem.tag_id;
			if(form.snipe.value == 1){
				requestData.snipe = form.snipe.value;
				requestData.snipe_domain = domain;
				requestData.snipe_strategy = form.snipe_strategy.value;
				requestData.snipe_without_strategy = snipe_without_strategy;
			}
			$.ajax({
				type:'post',
				url:'/autobid/add',
				data:requestData,
				dataType:'json',
				success:function(data){
					if(data.status == 'success'){
						$('#addKeywordModal').modal('hide');
						sem.clearAddKeyword();
						if($.cookie('firstAdd') == 1){
							$.cookie('firstAdd',0); 
							location.reload()	
						}else{
							sem.pageInitData(sem.page);
						}
					}else{
						var code = data.error_code;
						if (code < 7 || (code > 100 && code < 199)) {
							me.errTip(code);
							return;
						}

						if(code == 14){
							$err.html('所选竞价词数量超出'+sem.keyword_limit+'，请重新选择').show();
						}else if(code == 11){
							$err.html('请选择竞价词').show();
						}else if(code == 12){
							$err.html('缺少必要参数').show();
						}else if(code == 13){
							$err.html('请选择竞价地域').show();
						}else{
							$err.html('添加失败').show();
						}
					}
				}
			})

		})
	},
	//添加竞价词，获取关键词数据
	getKeywords:function(o){
		var ops = {user_id:sem.userId,page:1,plan_id:'',unit_id:'',keyword:'',type:0};
		$.extend(ops,o);
		$.ajax({
			type:'get',
			url:'/keyword/get',
			data:{"user_id":ops.user_id,plan_id:ops.plan_id,page:ops.page,unit_id:ops.unit_id,keyword:ops.keyword,type:ops.type},
			cache:false,
			dataType:'json',
			success:function(data){
				if(data.status=="success"){

					var arr = data.data.list;
					sem.currentKeyword = {};
					$.each(arr,function(i){
						sem.currentKeyword[arr[i].keyword_id] = arr[i];
					})
					if(data.data.page.count > 0){
						$('#keyword_bd').find('input[name="all"]').attr('disabled',false);
					}else{
						$('#keyword_bd').find('input[name="all"]').attr('disabled',true);
					}
					if(ops.callback){
						ops.callback(data.data)
					}
				}else{
					var errorCode = data.error_code,errorMessage = "";
					switch(errorCode){
						case "1":errorMessage="您还没有登录";break;
						case "2":errorMessage="账号不存在";break;
						case "3":errorMessage="账号已被停用";break;
						case "4":errorMessage="账号过期，请重新充值";break;
					}
					sem.modalAlert("无法获取到您的账户层级树，原因是："+errorMessage);
				}
			}
		});
	},
	//渲染关键词列表，并判断是否已选checked
	keywordList:function(data,choosed,page){
		var c = $.isEmptyObject(choosed),all = true,del = sem.choosed.del_keyword_id,reg = new RegExp(sem.choosed.del_keyword_id,'g');
		if(sem.choosed.isall.plan){
			$.each(data.list,function(i){
				if(!sem.choosed.del_keyword_id){
					data.list[i].checked = 'checked';
				}else{
					if(new RegExp(data.list[i].keyword_id).test(del)){
						data.list[i].checked = '';
						all = false;
					}else{
						data.list[i].checked = 'checked';
					}
				}
			})
		}else if(sem.choosed.isall.unit[sem.unitId]){
			$.each(data.list,function(i){
				if(!sem.choosed.del_keyword_id){
					data.list[i].checked = 'checked';
				}else{
					if(new RegExp(data.list[i].keyword_id).test(del)){
						data.list[i].checked = '';
						all = false;
					}else{
						data.list[i].checked = 'checked';
					}
				}
			})
		}else{
			if(!c){
				$.each(data.list,function(i){
					if(choosed[data.list[i].keyword_id]){
						data.list[i].checked = 'checked';
					}else{
						data.list[i].checked = '';
						all = false;
					}
				})
			}else{
				all = false;
			}
		}
		if(page > 1){
			$('#keyword_list').append(juicer($('#keywordsTpl').html(),data));
		}else{
			$('#keyword_list').html(juicer($('#keywordsTpl').html(),data));
		}
		if(all){
			$('#keyword_bd').find('input[name="all"]').attr('checked',true);
		}

		if(data.page.total_page > 1){
			$('#keyword_bd .page').show();
		}else{
			$('#keyword_bd .page').hide();
		}
		if(page >= data.page.total_page){
			$('#keyword_bd .page').hide();
		}
	},
	//选择关键词事件
	keywordHandle:function(){
		var $tips = $('#chooseKeyword').find('.err_tips');
		$('#keyword_bd').click(function(e){
			var elem = e.target;	
			if(elem.nodeName != 'INPUT' || elem.type != 'checkbox') return;
			var name = elem.name;
			if(name == 'single'){
				var reg = new RegExp(elem.value+',?','g');
				if(elem.checked){
					sem.choosed.keyword_id += elem.value+',';	
					sem.choosed.keyword[elem.value] = {keyword_id:elem.value,bid_status:elem.getAttribute('bid_status'),keyword:$(elem).parent().text()};
					sem.choosed.del_keyword_id = sem.choosed.del_keyword_id.replace(reg,'');

					sem.choosed.num += 1;
					if(elem.getAttribute('bid_status') == 1){
						sem.choosed.new_count += 1;
						
					}
					var all = true;
					$('#keyword_list input').each(function(i){
						if(!this.checked){
							all = false;
						}
					})
					if(all){
						$(this).find('input[name="all"]').attr('checked',true);
					}
				}else{
					sem.choosed.keyword_id = sem.choosed.keyword_id.replace(reg,'');
					sem.choosed.del_keyword_id += elem.value+',';
					delete sem.choosed.keyword[elem.value];
					sem.choosed.num -=1;
					if(elem.getAttribute('bid_status') == 1){
						sem.choosed.new_count -= 1;
					}
					$(this).find('input[name="all"]').attr('checked',false);
				}
			}else{
				var id = '';
				//全选
				if(elem.checked){
					if(sem.unitId){
						id = sem.unitId;
						if(!new RegExp(id).test(sem.choosed.unit_id)){
							sem.choosed.unit_id += sem.unitId+',';
						}
						sem.choosed.isall.unit[id] = true;
					}else{
						id = sem.planId;
						$('#'+id).parent().find('li').each(function(){
							sem.choosed.unit_id += this.id+',';
							sem.choosed.isall.unit[this.id] = true;
						})
						sem.choosed.isall.plan = true;
					}
					var count = 0,num = 0,new_count = 0;
					$('#keyword_list').find('input').each(function(){
						if(this.checked){
							count +=1;	
						}else{
							num +=1;	
							this.checked = true;	
							if(this.getAttribute('bid_status') == 1){
								new_count++;
							}
						}
					});
					var obj = sem.currentKeyword;
					$.extend(sem.choosed.keyword,obj);
					if(count == 0){
						sem.choosed.num += Number($('#'+id).attr('count'));
						sem.choosed.new_count += Number($('#'+id).attr('new_count'));
					}else{
						sem.choosed.num += num;
						sem.choosed.new_count += new_count;
					}
					var ids = '';
					$('#keyword_list input').each(function(){
						ids += this.value+',';
					})
					var delArr = sem.choosed.del_keyword_id.split(','),reg;
					$.each(delArr,function(index){
						reg = new RegExp(delArr[index]);
						if(reg.test(ids)){
							sem.choosed.del_keyword_id = sem.choosed.del_keyword_id.replace(delArr[index]+',','');

						}
					})
				}else{
					if(sem.unitId){
						sem.choosed.unit_id = sem.choosed.unit_id.replace(new RegExp(sem.unitId+',?'),'');
						id = sem.unitId;
						sem.choosed.isall.unit[id] = false;
						sem.choosed.isall.plan = false;
					}else{
						sem.choosed.unit_id = '';
						id = sem.planId;
						sem.choosed.isall.plan = false;
						sem.choosed.isall.unit = {};
					}
					$('#keyword_list').find('input').attr('checked',false);
					var choosed_o = sem.currentKeyword;
					sem.choosed.num -= Number($('#'+id).attr('count'));
					sem.choosed.new_count -= Number($('#'+id).attr('new_count'));
					$.each(choosed_o,function(key){
						delete sem.choosed.keyword[key];
					})
				}
			}
			$('#count').html(sem.choosed.num);
			if(Number(sem.choosed.new_count) + Number(sem.bidKeywordCount) > sem.keyword_limit){
				$tips.html('所选关键词数量超出'+sem.keyword_limit+'，请重新选择').show();	
			}else{
				$tips.html('').hide();
			}
			var n = sem.keyword_limit-sem.bidKeywordCount-sem.choosed.new_count;
			if(n >= 0){
				$('#new_count').html('当前还可选<b class="count">'+n+'</b>个').removeClass('t');
			}else{
				$('#new_count').html('所选关键词已超出<b class="count">'+ -n +'</b>个').addClass('t');
			}

		})
		$('#keyword_page').click(function(){
			var elem = this,index = Number($(elem).attr('index'))+1;
			sem.getKeywords({
				plan_id:sem.planId,
				unit_id:sem.unitId,
				page:index,
				choosed:sem.choosed.keyword,
				callback:function(data){
				elem.setAttribute('index',index);
				sem.keywordList(data,sem.choosed.keyword,index);
			}})
		})
		var $searchForm = $('#keyword_searchForm'),searchForm = $searchForm[0];			
		$searchForm.submit(function(e){
			e.preventDefault();
			if(sem.planId == ''){
				$tips.html('请选择计划').show();
				setTimeout(function(){
					$tips.html('').hide();
				},1000)
				return;
			}
			sem.getKeywords({
				plan_id:sem.planId,
				unit_id:sem.unitId,
				choosed:sem.choosed.keyword,
				keyword:searchForm.keyword.value,
				callback:function(data){
				sem.keywordList(data,sem.choosed.keyword);
			}})
		})
		//点击确认添加按钮,添加已选择关键词
		$('#add_choosedkeyword').click(function(){
			if(sem.choosed.num <= 0){
				$tips.html('请选择竞价词').show();
				setTimeout(function(){
					$tips.html('').hide();
				},2000)
				return;	
			}
			if(Number(sem.choosed.new_count) + Number(sem.bidKeywordCount) > sem.keyword_limit){
				$tips.html('所选竞价词已超过'+sem.keyword_limit+'个').show();
				setTimeout(function(){
					$tips.html('').hide();
				},2000)
				return;	
			}
			$('#mask-layer').hide();
			$('#add_keywordForm').find('.b-btn').show();
			var info ={};
			info.choosed = sem.choosed;
			sem.submitChoosed.num = sem.choosed.num;
			sem.submitChoosed.plan_id = sem.choosed.plan_id;
			sem.submitChoosed.unit_id = sem.choosed.unit_id;
			sem.submitChoosed.keyword_id = sem.choosed.keyword_id;
			sem.submitChoosed.del_keyword_id = sem.choosed.del_keyword_id;
			$('#choosed_keyword_list').html(juicer($('#choosed_keywordtpl').html(),info));
			$('.choosed-box .plan_name').text(sem.planName);
			$('#add_keyword_btn').hide();
			$('.choosed-box .count').text(sem.submitChoosed.num);
			$('.choosed-box').show();
			$('#chooseKeyword').modal('hide');
			sem.sumArea = Area.sumArea([sem.planRegion]);
			if(sem.sumArea.length > 0){	
				$('#regionLevel1').html(juicer($('#bidRegionTpl').html(),{region:sem.sumArea}));
				var selected = $('#regionLevel1').val(), op = '';
				$.each(sem.sumArea[selected]['childs'], function (key, val) {
					if (val.default) {
						op += '<option value="' + val.bid_id + '" selected>' + val.name + '</option>';
					} else {
						op += '<option value="' + val.bid_id + '">' + val.name + '</option>';
					}
				});
				$('#regionLevel2').html(op);
				if(sem.sumArea[selected].hide){
					$('#regionLevel2').hide();
				}else{
					$('#regionLevel2').show();
				}

			}
		})

		/*
		$('#chooseKeyword .close').click(function(){
			sem.clearAddKeyword('data');
		})
		*/
	
	},
	levelHandle:function(){
		$('#menu').die().click(function(e){
			var elem = e.target,
				type = elem.getAttribute('type');
			if(!type) return;
			var id = elem.getAttribute('id');
			var $keyword_bd = $('#keyword_bd');
			$keyword_bd.find('.page').hide();
			$(this).find('li').removeClass('on');
			$(this).find('p').removeClass('on');
			$(elem).addClass('on');

			switch(type){
				case 'switch':
                    $(elem).toggleClass("folder-open");
					$(elem).parents('.plan-li').find('.level-unit').toggleClass('hide');
				break;
				case 'plan':
                    $(elem).toggleClass("folder-open");
					$(elem).parents('.plan-li').find('.level-unit').toggleClass('hide');
					if(sem.planId != id){
						if(sem.choosed.plan_id != '' || sem.choosed.unit_id != '' || sem.choosed.keyword_id != ''){
							sem.buttferPlanid = elem.id;
							$('#confirmKeyword').find('.plan-name').text($(elem).text());
							$('#confirmKeyword').show();
							return;
						}
					}
					sem.planId = id;
					sem.unitId = '';
					sem.planName = $(elem).text();
					sem.unitName = '';
					sem.planRegion = $('#'+id).attr('region') ? $('#'+id).attr('region') : sem.userPromote_area;
					$('#current_plan').html(sem.planName);
					sem.getKeywords({
						plan_id:id,
						callback:function(data){
						sem.keywordList(data,sem.choosed.keyword);
					}})
				break;
				case 'unit':
					if(sem.planId != $(elem).parents('.plan-li').find('p').attr('id')){
						if(sem.choosed.plan_id != '' || sem.choosed.unit_id != '' || sem.choosed.keyword_id != ''){
							sem.buttferPlanid = elem.id;
							$('#confirmKeyword').find('.plan-name').text($(elem).text());
							$('#confirmKeyword').show();
							return;
						}
					}
					sem.planId = $(elem).parents('.plan-li').find('p').attr('id');
					sem.unitId = id;
					sem.planName = $(elem).parents('.plan-li').find('p').text();
					sem.unitName = $(elem).text();
					sem.planRegion = $('#'+sem.planId).attr('region') ? $('#'+sem.planId).attr('region') : sem.userPromote_area;
					$('#current_plan').html(sem.planName);
					sem.getKeywords({
						unit_id:id,
						choosed:sem.choosed.keyword,
						callback:function(data){
						sem.keywordList(data,sem.choosed.keyword);
					}})
				break;
			}
			$keyword_bd.find('input[name="all"]').attr('checked',false);
		})
		sem.keywordHandle();
		$('#continue_btn').click(function(){
			sem.clearAddKeyword('data');
			$('#count').html(0);
			$('#new_count').html('当前还可选'+(sem.keyword_limit-sem.bidKeywordCount)+'个');
			$('#menu').find('#'+sem.buttferPlanid).click();
			$('#confirmKeyword').hide();
			$('#choosed_keyword_list').html('');
		})
		$('#cancel_btn').click(function(){
			$('#confirmKeyword').hide();
		})
	},
	keepLevelHandle:function(){
		$('#menu').die().click(function(e){
			var elem = e.target,
				type = elem.getAttribute('type');
			if(!type) return;
			var id = elem.getAttribute('id');
			var $keyword_bd = $('#keyword_bd');
			$keyword_bd.find('.page').hide();
			$(this).find('li').removeClass('on');
			$(this).find('p').removeClass('on');
			$(elem).addClass('on');

			switch(type){
				case 'switch':
                    $(elem).toggleClass("folder-open");
					$(elem).parents('.plan-li').find('.level-unit').toggleClass('hide');
				break;
				case 'plan':
                    $(elem).toggleClass("folder-open");
					$(elem).parents('.plan-li').find('.level-unit').toggleClass('hide');
					sem.planId = id;
					sem.unitId = '';
					sem.planName = $(elem).text();
					sem.unitName = '';
					sem.planRegion = $('#'+id).attr('region') ? $('#'+id).attr('region') : sem.userPromote_area;
					$('#current_plan').html(sem.planName);
					sem.getKeywords({
						plan_id:id,
						type:1,
						callback:function(data){
						sem.keywordList(data,sem.choosed.keyword);
					}})
				break;
				case 'unit':
					sem.planId = $(elem).parents('.plan-li').find('p').attr('id');
					sem.unitId = id;
					sem.planName = $(elem).parents('.plan-li').find('p').text();
					sem.unitName = $(elem).text();
					sem.planRegion = $('#'+sem.planId).attr('region') ? $('#'+sem.planId).attr('region') : sem.userPromote_area;
					$('#current_plan').html(sem.planName);
					sem.getKeywords({
						unit_id:id,
						type:1,
						choosed:sem.choosed.keyword,
						callback:function(data){
						sem.keywordList(data,sem.choosed.keyword);
					}})
				break;
			}
			$keyword_bd.find('input[name="all"]').attr('checked',false);
		})
		sem.keepKeywordHandle();
	},
	//选择关键词事件
	keepKeywordHandle:function(){
		var $tips = $('#keepKeywords').find('.err_tips');
		$('#keyword_bd').click(function(e){
			var elem = e.target;	
			if(elem.nodeName != 'INPUT' || elem.type != 'checkbox') return;
			var name = elem.name;
			if(name == 'single'){
				var reg = new RegExp(elem.value+',?','g');
				if(elem.checked){
					sem.choosed.keyword_id += elem.value+',';	
					sem.choosed.keyword[elem.value] = {keyword_id:elem.value,bid_status:elem.getAttribute('bid_status'),keyword:$(elem).parent().text()};
					//sem.choosed.del_keyword_id = sem.choosed.del_keyword_id.replace(reg,'');

					sem.choosed.num += 1;
					$('#keepCount').html(sem.choosed.num);
					var all = true;
					$('#keyword_list input').each(function(i){
						if(!this.checked){
							all = false;
						}
					})
					if(all){
						$(this).find('input[name="all"]').attr('checked',true);
					}
				}else{
					sem.choosed.keyword_id = sem.choosed.keyword_id.replace(reg,'');
					//sem.choosed.del_keyword_id += elem.value+',';
					delete sem.choosed.keyword[elem.value];
					sem.choosed.num -=1;
					$('#keepCount').html(sem.choosed.num);
					$(this).find('input[name="all"]').attr('checked',false);
				}
				if(sem.choosed.num > sem.keyword_limit){
					$tips.html('所选关键词数量超出'+sem.keyword_limit+'，请重新选择').show();	
				}else{
					$tips.html('').hide();
				}
			}else{
				var id = '';
				//全选
				if(elem.checked){
					if(sem.unitId){
						sem.choosed.unit_id += sem.unitId+',';
						id = sem.unitId;
					}else{
						sem.choosed.plan_id = sem.planId;
						id = sem.planId;
					}
					var count = 0,num = 0,new_count = 0;
					$('#keyword_list').find('input').each(function(){
						if(this.checked){
							count +=1;	
						}else{
							num +=1;	
							this.checked = true;	
							sem.choosed.keyword_id += this.value+',';	
							sem.choosed.keyword[this.value] = {keyword_id:elem.value,keyword:$(this).parent().text()};
						}
					});
					var obj = sem.currentKeyword;
					$.extend(sem.choosed.keyword,obj);
					if(count == 0){
						sem.choosed.num += Number($('#'+id).attr('count'));
					}else{
						sem.choosed.num += num;
					}
					var ids = '';
					$('#keyword_list input').each(function(){
						ids += this.value+',';
					})
					/*
					var delArr = sem.choosed.del_keyword_id.split(','),reg;
					$.each(delArr,function(index){
						reg = new RegExp(delArr[index]);
						if(reg.test(ids)){
							sem.choosed.del_keyword_id = sem.choosed.del_keyword_id.replace(delArr[index]+',','');

						}
					})
					if(sem.unitId){
						sem.choosed.isall.unit[id] = true;
					}else{
						sem.choosed.isall.plan = true;
					}
					*/
					$('#keepCount').html(sem.choosed.num);
				}else{
					if(sem.unitId){
						sem.choosed.unit_id = sem.choosed.unit_id.replace(sem.unitId+',','');
						id = sem.unitId;
					}else{
						sem.choosed.plan_id = '';
						id = sem.planId;
					}
					$('#keyword_list').find('input').attr('checked',false);
					var choosed_o = sem.currentKeyword;
					sem.choosed.num -= Number($('#'+id).attr('count'));
			//		sem.choosed.new_count -= Number($('#'+id).attr('new_count'));
					$('#keepCount').html(sem.choosed.num);
					$.each(choosed_o,function(key){
						delete sem.choosed.keyword[key];
					})
				}
				if(sem.choosed.num > sem.keyword_limit){
					$tips.html('所选关键词数量超出'+sem.keyword_limit+'，请重新选择').show();	
				}else{
					$tips.html('').hide();
				}
			}

		})
		//点击确认保留按钮,添加已选择关键词
		$('#keepKeyword_btn').click(function(){
			if(sem.choosed.num == 0){
				$tips.html('请选择关键词').show();
				setTimeout(function(){
					$tips.html('').hide();
				},2000)
				return;	
			}
			if(sem.choosed.num > sem.keyword_limit){
				$tips.html('所选竞价词已超过'+sem.keyword_limit+'个').show();
				setTimeout(function(){
					$tips.html('').hide();
				},2000)
				return;	
			}
			//提交表单
			$.ajax({
				type:'get',
				url:'/autobid/del/except',
				data:{user_id:sem.userId,keyword_ids:sem.choosed.keyword_id.replace(/,?$/,'')},
				dataType:'json',
				success:function(data){
					if(data.status =="success"){
						location.reload();
					}else{
						var code = data.error_code;
						if (code < 7 || (code > 100 && code < 199)) {
							me.errTip(code);
							return;
						}

						if(code == 13){
							$tips.html('删除竞价词失败').show();
						}else if(code == 14){
							$tips.html('保留的竞价词超过'+sem.keyword_limit+'个').show();
						}
					
					}
				}
				
			})
		})

	},
	//清除已选择的关键词并删除要删除div中的事件
	//a =='data' 仅删除关键词数据
	clearAddKeyword:function(a){
		sem.choosed.num = 0;
		sem.choosed.new_count = 0;
		sem.choosed.plan_id = '';
		sem.choosed.unit_id = '';
		sem.choosed.keyword_id = '';
		sem.choosed.del_keyword_id = '';
		sem.choosed.keyword = {};
		sem.choosed.isall.plan = '';
		sem.choosed.isall.unit = {};
		sem.submitChoosed.num = 0;
		sem.submitChoosed.plan_id = '';
		sem.submitChoosed.unit_id = '';
		sem.submitChoosed.keyword_id = '';
		sem.submitChoosed.del_keyword_id = '';
		if(a == 'data') return;
		$('#menu').unbind('click');
		$('#add_keyword_btn').unbind('click');
		$('#keyword_bd').unbind('click');
		$('#keyword_page').unbind('click');
		$('#keyword_searchForm').unbind('submit');
		$('#add_choosedkeyword').unbind('click');
		$('#continue_add').unbind('click');
		$('#sinpie_switchopen').unbind('click');
		$('#openCheck').unbind('click');
		$('#all_clear').unbind('click');
		$('#addKeywordModal').remove();
		$('#chooseKeyword').remove();
	},
    /*
     * url 请求url   --必填
     * container 容器id  -- 必填
     * tpl  模板id  --必填
     * page 页码
     * page_size 每页条数
     * orderby 排序
     * ascdesc asc(倒序)/desc(倒序)
     */
    getListData: function (o) {
        var me = this, ops = {container: 'list', page: 1, page_size: 20, requireData: '', orderby: '', ordertype: '', selectAll: true, plan_id: me.planId, unit_id: me.unitId};

        $.extend(ops, o);
        var userdata = 'user_id=' + me.userId;
        if (me.tag_id) {
            userdata += '&tag_id=' + me.tag_id;
        }
        var requireData = '';

        var $pageBox = $('#' + ops.container + ' .pager-box');
        if (!o.page_size) {
            if ($pageBox.length > 0) {
                ops.page_size = $pageBox.find('select').val();
            }
        }
        if (!o.page) {
            if ($pageBox.length > 0) {
                ops.page = $pageBox.find('.num').text();
            }
        }
        if (ops.requireData) {
            requireData = ops.requireData + '&' + userdata + '&page_size=' + ops.page_size + '&page=' + ops.page; 
        } else {
            requireData = userdata + '&page_size=' + ops.page_size + '&page=' + ops.page;
        }
        $.ajax({
            type: 'get',
            url: ops.url,
            data: requireData,
            dataType: 'json',
			async:false,
            cache: false,
            success: function (data) {
                if (data.status !== 'success') {
                    var code = data.error_code;
                    if (code < 7 || (code > 100 && code < 199)) {
                        me.errTip(code);
                    }
                    return;
                }
                var info = data.data, tpl = $('#' + ops.tpl).html();
                info.plan_id = ops.plan_id;
                info.unit_id = ops.unit_id;
                info.is_agent = me.is_agent;
                ops.data = info;
                info.access = me.access_list;
                if (ops.url == '/competitor/feed') {
                    info.list = Area.fillBidRegion(info.list, 'track_area', 'competitors');
                    info.list = me.filterCompetitordomain(info.list, 'domain')
                }
                if (ops.url == '/autobid/list') {
                    if(info.page.count == 0 && me.tag_id == "" && ops.requireData.indexOf("flag=1") < 0){
                        $(".list-exist").hide();
                        $("#add_bid").css("z-index","100");
                        $("body").append('<div style="position:fixed;z-index:99;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.2)"><img src="/static/img/sem/guide0.png" width="727" height="210" style="margin:70px 0 0 85px;" /> </div>');
						$('.fixed-area').removeClass('fixed-area');
						$('#autobid_list').css({'margin-top':0});
                        $.cookie("firstAdd","1");
						$('#' + ops.container).html(juicer(tpl, info));
						sem.bidKeywordCount = info.page.count;
                        return false;
                    }
                    info.list = Area.fillBidRegion(info.list);
					info.list = me.fillRank(info.list);
					sem.getKeywordsRound(info.list);
					sem.refreshKeywords = sem.filterKeywordBidstatus(info.list); 
					sem.bidKeywordCount = info.page.count;
                }

                $('#' + ops.container).html(juicer(tpl, info));
                var pageInfo = {page_size: info.page.page_size,count:info.page.count, requireData: ops.requireData, cur_page: info.page.cur_page, total_page: info.page.total_page, container: ops.container, url: ops.url, tpl: ops.tpl}
                if (info.page.total_page) {
                    me.pager(pageInfo);
                }
                if (ops.callback) {
					ops.callback();
                }
				/*
                var $table = $('#' + ops.container).find('table');

                $table.click(function (e) {
                    var elem = e.target;
                    if (elem.nodeName != 'TH' || !/sc/g.test(elem.className)) return;

                    var orderby = elem.getAttribute('orderby'), ordertype = /desc/.test(elem.className) ? 'desc' : 'asc', pagesize = $('#' + ops.container).find('.pager-box select').val();
                    me.getListData({container: ops.container, page_size: pagesize, tpl: ops.tpl, url: ops.url, requireData: ops.requireData, orderby: orderby, ordertype: ordertype});
                });

				*/
                if (ops.selectAll) {
                    me.selectFun(ops.container);
                }
                Base.handleTooltips();
            }
        })
    },
    pager: function (o) {
        var ops = {}, me = this;
        $.extend(ops, o);
        ops.prev_page = ops.cur_page - 1;
        ops.next_page = ops.cur_page + 1;
        var pageTpl = $('#pagerTpl').html();

        $('#' + ops.container).append(juicer(pageTpl, ops));

        var $pageSize = $('#' + ops.container).find('.pager-box select');
        $pageSize.change(function () {

            me.getListData({url: ops.url, page: 1, container: ops.container, page_size: $(this).val(), tpl: ops.tpl, requireData: ops.requireData});
            $.cookie('size', $(this).val(), {'path': '/', expires: 365})
        })
        $('#' + ops.container).find('ul').click(function (e) {
            var elem = e.target;
            e.preventDefault();
            if (elem.nodeName != 'A' || /disabled/g.test($(elem).parent().attr('class'))) return;

            var index = parseInt(elem.getAttribute('index'));
            me.getListData({url: ops.url, page: index, container: ops.container, page_size: $pageSize.val(), tpl: ops.tpl, requireData: ops.requireData})
        })

    },
	fillRank:function(data){
		var t = false;
		
		$.each(data,function(i){
			if(t) return true;
			if(data[i].rank != '--'){
				data[i].rank_tip = true;
				t = true;
			}
		})
		return data;
	},
    selectFun: function (container) {
        var me = this, $container = $('#' + container);
        $container.find('table').click(function (e) {
            var elem = e.target;
            if (elem.nodeName != 'INPUT') return;
            if (elem.name == 'all') {
                if (elem.checked) {
                    $container.find('input[name="ids"]').attr('checked', true);
                    var arr = [];
                    $container.find('input[name="ids"]').each(function () {
                        arr.push(this.value);
                    })
                    elem.value = arr.join(',');

                } else {
                    $container.find('input[name="ids"]').attr('checked', false);
                    elem.value = '';
                }
            } else {
                var all = $container.find('input[name="all"]')[0];
                if (elem.checked) {
                    all.value += elem.value + ',';

                } else {
                    var reg = new RegExp(elem.value + ',?', 'g');
                    all.value = all.value.replace(reg, '');
                    if (all.checked) {
                        all.checked = false;
                    }
                }

            }
        });
        me.isClick = true;
    },
    modalAlert: function (text) {
        var info = {text: text}, tpl = $('#modalHintTpl').html();
		if (text !== 'hide') {
			$('#modal').html(juicer(tpl,{text:text}));
			$('#modal').modal();
		}else{
			$('#modal').modal('hide');
		}
    },
    errHint: function (text) {
        var info = {text: text}, tpl = $('#modalHintTpl').html();
        if (text !== 'hide') {
            $('#page_err').html(info.text).show();
			setTimeout(function(){
				$('#page_err').hide();
			},2000)
        }else{
			$('#page_err').hide();
        }
    },
    checkSelected: function (o) {
        var me = this, ops = {};
        $.extend(ops, o);
        ops.ids = $('#' + ops.container).find('input[name="all"]').val().replace(/,$/, '');
        ops.user_id = me.userId;
        if (!ops.ids) {
			$('#page_err').html(ops.hint).show();
			setTimeout(function(){
				$('#page_err').hide();
			},2000)
            return false;
        } else {
            return true;
        }
    },
    handleFun: function (o) {
        var me = this, ops = {status: '', type: '2', field: '', pause: '', param: '', value: ''};//type：1=》只判断有选中 2=》操作 删除；暂停/启用;field:修改字段 pause 

        $.extend(ops, o);
        if (ops.url !== '/autobid/del_tag') {
            ops.ids = $('#' + ops.container).find('input[name="all"]').val().replace(/,$/, '');
            if (!ops.ids) {
                me.modalAlert(ops.hint);
                return false;
            }
        }
        ops.user_id = me.userId;
        if (ops.type == '1') return true;

        $('#modal').html(juicer($('#modalHandleTpl').html(), ops));
        $('#modal').modal();
        var $form = $('#hindleForm');
        $form.submit(function (e) {
            e.preventDefault();
            var form = this, err = $('#err');
            $.ajax({
                url: form.action,
                type: form.method,
                data: $form.serialize(),
                dataType: 'json',
                success: function (data) {
                    if (data.status == 'success') {
                        $('#modal').modal('hide');
                        if (ops.callback) {
                            ops.callback();
                        }
                    } else {
                        if (data.error_code == '1') {
                            location.href = '/';
                        }
                        err.html('操作失败').addClass('err');
                    }
                }
            });

        });
    },
	//获取所选关键词地域合集
    getCrossArea: function (o) {
        var me = this, ops = {container: 'autobid_list', id: ''}, $container = $('#' + ops.container),
            ids, crossArea = [], area = [], a, b, c, d = [], bid_area = [];
        $.extend(ops, o);
        if (ops.id == "") {
            ids = $container.find('input[name="all"]').val().replace(/,?$/, '').split(',');
        } else {
            ids = [ops.id];
        }

        if (!me.planId && ids.length > 1) {
            for (var i = 0, l = ids.length; i < l; i++) {
                if (!$container.find('input[value="' + ids[i] + '"]').attr('region')) {
                    area.push(sem.userPromote_area);
                } else {
                    area.push($container.find('input[value="' + ids[i] + '"]').attr('region'));
                }
                bid_area.push($container.find('input[value="' + ids[i] + '"]').attr('bidarea'))
            }
        } else {
            if (!$container.find('input[value="' + ids[0] + '"]').attr('region')) {
                area.push(sem.userPromote_area);
            } else {
                area.push($container.find('input[value="' + ids[0] + '"]').attr('region'));
            }
            for (var i = 0, l = ids.length; i < l; i++) {
                bid_area.push($container.find('input[value="' + ids[i] + '"]').attr('bidarea'))
            }
        }
        var b_id = '';
        for (var i = 0, len = bid_area.length; i < len; i++) {
            if (b_id == '') {
                b_id = bid_area[i];
            }
            if (bid_area[i] != b_id) {
                b_id = '';
                break;
            }
        }
        var o = Area.sumArea(area, b_id);
        return o;
    },
    getCalcids: function () {
        var me = this, ids, ops = {container: 'autobid_list', id: ''}, $container = $('#' + ops.container),
            ids = $container.find('input[name="all"]').val().replace(/,?$/, '').split(','), bidStatus = {}, calcids = [];

        for (var i = 0, l = ids.length; i < l; i++) {
            bidStatus[ids[i]] = $container.find('input[value="' + ids[i] + '"]').attr('bid_status');
        }

        for (var j in bidStatus) {
            if (bidStatus.hasOwnProperty(j)) {
                if (bidStatus[j] !== '2') {
                    calcids.push(j);
                }
            }
        }

        return calcids;

    },
    /*
     *o.status: on 获取开启跟踪ids 2、3  ，off 获取未开启跟踪ids 1
     * */
    getCompetitor: function (o) {
        var me = this, ids, ops = {container: 'autobid_list', id: ''}, $container = $('#' + ops.container),
            ids = $container.find('input[name="all"]').val().replace(/,?$/, '').split(','), competitorStatus = {}, keywordids = [],
            s = 1;
        $.extend(ops, o);

        for (var i = 0, l = ids.length; i < l; i++) {
            competitorStatus[ids[i]] = $container.find('input[value="' + ids[i] + '"]').attr('competitor_status');
        }

        for (var j in competitorStatus) {
            if (competitorStatus.hasOwnProperty(j)) {
                if (ops.status == 'off') {
                    if (competitorStatus[j] == s) {
                        keywordids.push(j);
                    }
                } else {
                    if (competitorStatus[j] != s) {
                        keywordids.push(j);
                    }

                }
            }
        }
        return keywordids;
    },
    initTag: function () {
        var me = this;
        $.ajax({
            url: '/autobid/taglist',
            data: 'user_id=' + me.userId,
            dataType: 'json',
            success: function (data) {
                if (data.status == 'success') {
                    var info = data, listTpl = $('#tagListTpl').html(), treeTpl = $('#tagMenuTpl').html();
					info.tag_id = sem.tag_id;
                    $('.tag-list').html(juicer(listTpl, info));
                    $('#tagMenu').html(juicer(treeTpl, info));
                } else {
                    me.modalAlert('标签列表更新失败');
                }
            }
        })
    },
    errTip: function (code) {
        if (code == 1) {
            location.reload();
        }
        var codeArr = {
            '1': '未登录',
            '2': '账号不存在',
            '3': '账号已被停用',
            '4': '账号过期，请重新充值',
            '5': '绑定的sem用户不存在',
            '6': '未被初始化',
            '100': '账户更新中...',
            '101': '对手跟踪未购买或过期',
            '102': '没有权限'
        }
        sem.modalAlert(codeArr[code]);
        switch (code) {
            case 1:
                location.href = '/';
                break;
            case 102:
                window.close();
                break;
        }
    },
    initWhitehandle: function () {
        var me = this;
        $('#addWhitelistForm').submit(function (e) {
            e.preventDefault();
            var form = this, domainVal = form.domains.value, domain, $err = $('#err_white');
            if (!/^http:\/\//.test(domainVal)) {
                domain = 'http://' + domainVal;
            } else {
                domain = domainVal;
            }

            if (domainVal == '') {
                $err.html('请输入白名单网址');
                return;
            } else if (!RegTool.urlReg.test(domain)) {
                $err.html('白名单网址格式错误');
                return;
            }
            $err.html('');

            $.ajax({
                url: form.action,
                type: form.method,
                data: 'domains=' + domain + '&user_id=' + sem.userId,
                dataType: 'json',
                success: function (data) {
                    if (data.status == 'success') {
                        me.initWhitelist();
						form.domains.value = '';
                    } else {
                        var code = data.error_code;
                        switch (code) {
                            case '7':
                            case '8':
                                $err.html('url校验失败');
                                break;
                            case '9':
                                $err.html('白名单超过5个');
                                break;
                            default:
                                $err.html('添加失败');
                        }
                    }
                }
            })
        });

        $('#whiteCheck').click(function () {
            var domain = $('#addWhitelistForm')[0].domains.value;
            if (domain == '') {
                $('#err_white').html('请输入网址');
                return false
            }
            this.href = 'http://www.baidu.com/#wd=' + domain;
        });

        $('#white_list').click(function (e) {
            var elem = e.target;
            if (elem.nodeName != 'SPAN') return;
            var domains = elem.getAttribute('name');
            $.ajax({
                url: '/user/del_whitelist',
                type: 'get',
                data: 'user_id=' + me.userId + '&domains=' + domains,
                dataType: 'json',
                success: function (data) {
                    if (data.status == 'success') {
                        $(elem).parent().remove();
                    }
                }
            })
        })
    },
    initWhitelist: function (type) {
        var me = this,tpl = $('#whiteListtpl').html();
		if(type == 'init'){
			$('#white_list').html(juicer(tpl,sem.userInfoData));
			return;
		}
        $.ajax({
            url: '/user/info',
            type: 'get',
            data: 'user_id=' + me.userId,
            dataType: 'json',
            success: function (data) {
                if (data.status == 'success') {
                    var info = data.data;
                    $('#white_list').html(juicer(tpl, info));
                }
            }
        })
    },
    syncUserData: function () {
        if ($("#updateLayer").length == 0) {
            var initTpl = $('#initUserDatatpl').html();

            $("body").append(initTpl);
        }
        $updateLayer = $('#updateLayer');
        $updateLayer.modal();
        var init_flag = 0,
            requestHzuser = function () {
                var f = function () {
                    init_flag = getHzUserInfo().init_flag;
                    if (init_flag != '2') {
                        requestHzuser();
                    } else {
                        $updateLayer.find(".modal-body").html('<div class="ok-layer"><div class="modal-left"><i class="bid-icon ok"></i></div><div class="modal-raight"><p class="margin-top-10">账户结构数据同步成功</p></div></div>');
                        setTimeout(function () {
                            location.reload();
                        }, 2000)
                    }
                };
                setTimeout(f, 3000);
            };

		setTimeout(function(){
			init_flag = getHzUserInfo().init_flag;
			if(init_flag == 0){
				updateAccountData();	
			}else{
				requestHzuser();
			}			
		},1500)
	
        if (init_flag != '2') {
            setTimeout(function () {
                $('#userDatahint').html('您的关键词数量较多，同步时间可能较长，<br/>请您耐心等待......');
            }, 60000)
        }
    },
    filterCompetitordomain: function (data, name) {
        for (var i in data) {
            data[i].domains = '';
            var d = data[i]['competitors'];
            for (var j = 0, len = d.length; j < len; j++) {
                data[i]['competitors'][j]['domain_name'] = data[i]['competitors'][j]['domain'].replace(/^www\./, '');
                if (j == len - 1) {
                    data[i].domains += data[i]['competitors'][j]['domain'];
                } else {
                    data[i].domains += data[i]['competitors'][j]['domain'] + ',';
                }
            }
        }

        return data;
    },
	filterKeywordBidstatus:function(list){
		var arr = [];
		$.each(list,function(i){
			if(list[i].bid_status != 3){
			arr.push(list[i].keyword_id);
			}
		
		})
		return arr.join(',');
	},
	refreshBidList:function(){
		var me = this;
		if(!me.refreshKeywords) return;
		var keyword_ids = me.refreshKeywords.replace(/,?$/,'');
		$.ajax({
			type:'get',
			url:'/autobid/curr_info',
			data:'user_id='+me.userId+'&keyword_ids='+keyword_ids,
			dataType:'json',
			success:function(data){
				if(data.status == 'success'){
				var $tr = '',$input = '';
					$.each(data.data,function(key,val){
						$tr = $('#tr_'+key);
						if (me.roundKeywords[key].pause_autobid != val.pause_autobid){
							me.roundKeywords[key].pause_autobid = val.pause_autobid;
							$tr.find('input[name="id"]').attr('bid_status',val.bid_status);
							var htmlStatus = '';
							if(val.bid_status == 3){
								me.refreshKeywords = me.refreshKeywords.replace(new RegExp(val.keyword_id+',?'),'');
							}
							if(val.bid_status == 2){
								if (val.pause_autobid == 0){
										htmlStatus = '<span class="bid-icon-status bid-open refresh"><span class="bid-icon-text">竞价中</span> <span class="bid-btn bid-operate"><i class="hzicon"></i></span></span>';
										$tr.removeClass('stop');
								}else if (val.pause_autobid == 2){
										htmlStatus = '<span class="bid-icon-status bid-open bid-waiting refresh"><span class="bid-icon-text">等待竞价</span> <span class="bid-btn bid-operate"><i class="hzicon"></i></span></span>';
										$tr.removeClass('stop');
								}else{
									htmlStatus = '<span class="bid-icon-status bid-open bid-stop refresh"><span class="bid-icon-text">关键词停投</span> <span class="bid-btn bid-operate"><i class="hzicon"></i></span></span>';
										$tr.addClass('stop');
								}
							}else if(val.bid_status == 3){
								htmlStatus = '<span class="bid-icon-status bid-pause"><span class="bid-icon-text">竞价暂停</span> <span class="bid-btn bid-operate"><i class="hzicon"></i></span></span>';
								$tr.addClass('stop');
							}

							$tr.find('.bid-status').html(htmlStatus);
							$tr.find('.bid-icon-status').addClass('refresh-translate');
						}
						if(val.round > me.roundKeywords[key].round){
							me.roundKeywords[key].round = val.round;
							me.roundKeywords[key].pause_autobid = val.pause_autobid;
							$tr.find('input[name="id"]').attr('bid_status',val.bid_status);
							var htmlStatus = '';
							if(val.bid_status == 3){
								me.refreshKeywords = me.refreshKeywords.replace(new RegExp(val.keyword_id+',?'),'');
							}
							if(val.bid_status == 2){
								if (val.pause_autobid == 0){
										htmlStatus = '<span class="bid-icon-status bid-open refresh"><span class="bid-icon-text">竞价中</span> <span class="bid-btn bid-operate"><i class="hzicon"></i></span></span>';
										$tr.removeClass('stop');
								}else if (val.pause_autobid == 2){
										htmlStatus = '<span class="bid-icon-status bid-open bid-waiting refresh"><span class="bid-icon-text">等待竞价</span> <span class="bid-btn bid-operate"><i class="hzicon"></i></span></span>';
										$tr.removeClass('stop');
								}else{
									htmlStatus = '<span class="bid-icon-status bid-open bid-stop refresh"><span class="bid-icon-text">关键词停投</span> <span class="bid-btn bid-operate"><i class="hzicon"></i></span></span>';
										$tr.addClass('stop');
								}
							}else if(val.bid_status == 3){
								htmlStatus = '<span class="bid-icon-status bid-pause"><span class="bid-icon-text">竞价暂停</span> <span class="bid-btn bid-operate"><i class="hzicon"></i></span></span>';
								$tr.addClass('stop');
							}

							$tr.find('.bid-status').html(htmlStatus);

							if(val.rank > 10 && val.rank < 30){
							$tr.find('.rank').html('<i type="photo" class="cursor photo" keyword="'+val.keyword+'" rank="'+val.rank+'" target_rank="'+val.target_rank+'">'+val.rank.replace(/^(.)/,function($1){return ($1 == '1') ? '左':'右'})+'</i>')
							}else{
								$tr.find('.rank').html('<span class="refresh">--</span>');
							}
							
							var priceHtml = '--';
							if(val.price){
								priceHtml = '<span class="refresh">'+val.price+'</span>';
							}
							$tr.find('.price').html(priceHtml);
							var feedbackHtml = '';
							if(val.complete_feedback > 1){
								var i = Number(val.complete_feedback);
								switch(i){
									case 2:
										feedbackHtml = '<span class="font-ok">出价成功</span>';
									break;
									case 3:
										feedbackHtml = '<span>';
										var a = Number(val.pause_reason);
										switch(a){
											case 201:
												feedbackHtml += '非推广时段';
											break;
											case 202:
											case 311:
												feedbackHtml += '计划暂停';
											break;
											case 203:
												feedbackHtml += '计划达到预算';
											break;
											case 211:
												feedbackHtml += '账户达到预算';
											break;
											case 301:
												feedbackHtml += '单元暂停';
											break;
											case 401:
												feedbackHtml += '关键词暂停';
											break;
											case 2:
												feedbackHtml += '创意无效';
											break;
											case 1:
												feedbackHtml += '竞价地域无效';
											break;
											case 111:
												feedbackHtml += '账户预算不足';
											break;
											case 103:
												feedbackHtml += '账户余额为零';
											break;
											case 104:
												feedbackHtml += '账户未通过审核';
											break;
											case 106:
												feedbackHtml += '账户审核中';
											break;
											case 107:
												feedbackHtml += '账户被禁用';
											break;
											default:
												feedbackHtml += '关键词未投放<i class="bid-icon tip tooltips" data-trigger="hover" data-placement="top" data-original-title="请查看百度推广账号设置"></i>';
										}
										feedbackHtml += '</span>';
									break;
									case 4:
										feedbackHtml = '<span>客户端离线，竞价暂停</span>';
									break;
									case 5:
										feedbackHtml = '<span class="font-ok">首页未发现竞争对手</span>';
									break;
									case 6:
										feedbackHtml = '<span class="font-ok">首页无推广广告</span>';
									break;
									case 7:
										feedbackHtml = '<span >限价过低</span><i class="bid-icon tip tooltips" data-trigger="hover" data-placement="top" data-original-title="采用限价,争取排名"></i>';
									break;
									case 8:
										feedbackHtml = '<span >竞价词未触发,还原出价<i class="bid-icon tip tooltips" data-trigger="hover" data-placement="top" data-original-title="相同时段和地域存在相     同的关键词，百度推广将优先使用出价高的达标关键词，如果您开启了智能竞价，当前关键词出价将会自动调整为上一轮出价"></i></span>';
									break;
									case 9:
										feedbackHtml = '<span class="font-ok">调整排名，出价成</span>';
									break;
								}
								$tr.find('.feedback-td').html('<span class="refresh"><i class="time">'+val.complete_time+'</i>'+feedbackHtml+'</span>');
							}else{
								$tr.find('.feedback-td').html('<span class="refresh"></span>');
							}
							$tr.find('.refresh').addClass('refresh-translate');
							//$tr.find('.refresh').removeClass('refresh-translatea');
						}
					})
					setTimeout(function(){
						$('#autobid_list').find('.refresh-translate').removeClass('refresh-translate');
					},500)

					Base.handleTooltips();
				}
			}
		})
	},
    access_list: {},
    customer_access: function (callback) {
        var me = this;
        $.ajax({
            type: 'get',
            url: '/hzuser/module_access',
            dataType: 'json',
            async: false,
            success: function (data) {
                if (data.status == 'success') {
                    var info = data.data
                    $.each(info, function (index) {
                        me.access_list[info[index]['id']] = info[index];
                    })

                    if (callback) {
                        callback(data);
                    }
                } else {
                    var code = data.error_code;
                    me.errTip(code);
                }
            }
        })
    },
    pageInitData: function (page) {
        var me = this;
        switch (page) {
            case 'smart':
                me.getListData({url: '/autobid/list', container: 'autobid_list', page: 1, tpl: 'smartBidListTpl'});
                break;
            case 'ref':
                me.getListData({url: '/refbid/feed', container: 'autobid_list', page: 1, tpl: 'smartRefListTpl', callback: function () {
                    calculating = this.data.calculating;
                    getCalculat();
                }});
                break;
            case 'competitor':
                if (competitor_expiration == 2) {
                    calculating = 0;
                    $switchConfition.hide();
                    me.getListData({url: '/competitor/feed', container: 'autobid_list', page: 1, tpl: 'competitorListTpl'});
                } else {
                    $competitor = $('#competitor_expiration'),
                        $competitorTip = $('#competitortips');
                    $('#filter_layer').hide();
                    $('#tab_content').hide();
                    $competitor.height($('#levelTree').height());
                    $competitor.show();
                    $competitorTip.show();
                }
                break;
            default:
                me.getListData({url: '/autobid/list', container: 'autobid_list',page:1, tpl: 'smartBidListTpl'});
        }
    },
    page: ''
};


$(function () {
    S = sem;
    //hzUserInfoData = getHzUserInfo();
    hzUserInfoData = sem.hzuserInfoData = getHzUserInfo();
    sem.userId = sem.hzuserInfoData.default_bind_user;
    sem.is_agent = sem.hzuserInfoData.is_agent;
	sem.keyword_limit = sem.hzuserInfoData.keyword_limit;
	if(sem.hzuserInfoData.init_flag == 2){
        if (sem.is_agent == 1) {
            sem.customer_access();
            if (sem.access_list[2].access == 1) {
                $('.tab-pane').hide();//隐藏功能设置栏				
                $('.myacount').find('.btn-set').hide();//隐藏账户同步按钮
            }
        }
        sem.userInfoData = getSemUserInfo().data;
        sem.name = sem.userInfoData.name;
        sem.userPromote_area = sem.userInfoData.promote_area;
        $(".nickname").text(sem.hzuserInfoData.name);//填充导航栏昵称
        $('#updateAccountData').find('.modal-bd').html('上次数据更新时间：' + sem.userInfoData.last_sync);
        commonInit();
	}else{
        /* 用户进入搜索管理页面后，首先请求用户信息接口/hzuser/info
         * 通过init_flag字段判断是否初始化数据，0表示未初始化数据，
         * 则用户不能进行其他操作，需等待初始化数据完成，完成后可以进行之后的操作
         */
        updateAccountData();//这里要改成调用user/init接口
	}
});
