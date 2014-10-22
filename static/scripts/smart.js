//获取筛选条件
function getFilter() {
    var $form = $("#smartFilterForm")[0],
        searchData = "",
        keyword = $form.keyword.value;

    searchData = "keyword=" + keyword+"&flag=1";
    return searchData;
}
var tagRefeshList = function () {
    if (sem.page == 'smart') {
        S.getListData({url: '/autobid/list', container: 'autobid_list', tpl: 'smartBidListTpl'});
    } else {
        S.getListData({url: '/refbid/feed', container: 'autobid_list', tpl: 'smartRefListTpl', callback: function () {
            calculating = this.data.calculating;
            getCalculat();
        }});
    }
};

//获取筛选后更新列表
function getFilterList() {
    var searchData = getFilter(),
        url = '',
        tpl = '',
        page = sem.page;
    switch (page) {
        case 'smart':
            url = "/autobid/list";
            tpl = "smartBidListTpl";
            break;
        case 'ref':
            url = "/refbid/feed";
            tpl = "smartRefListTpl";
            break;
        case 'competitor':
            url = "/competitor/feed";
            tpl = "competitorListTpl";
            break;
    }
    if (!searchData) {
        return false;
    }
    S.getListData({url: url, container: 'autobid_list', tpl: tpl, requireData: searchData});
}

//生成开启竞价和竞价设置浮层
function createBidLayer(data) {
    var data = data;
    $("#modalOpenBidLayer").html(juicer($("#openBidTpl").html(), data));
    $("#modalOpenBid").modal();
	var $form = $('#open_bidform'),form = $form[0],$li = $('.choose-rank li'),rank='';
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
	$('#sinpie_switch').click(function(){
		if(this.checked){
			this.value = 1;
			form.snipe_strategy.disabled = false;
			$form.find('.snipe-box').show();
		}else{
			this.value = 0;
			form.snipe_strategy.disabled = true;
			$form.find('.snipe-box').hide();
		}
	})
	var $err = $('#modalOpenBid').find('.err_tips');
	$('#setCheck').click(function(){
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
    var areaData = data.region;
    var selected = $('#bidRegion').val(), op = '';
    $.each(areaData[selected]['childs'], function (key, val) {
        if (val.default) {
            op += '<option value="' + val.bid_id + '" selected>' + val.name + '</option>';
        } else {
            op += '<option value="' + val.bid_id + '">' + val.name + '</option>';
        }
    });
    $('#bidLevel2').html(op);
    if (areaData[selected].hide) {
        $('#bidLevel2').hide();
    } else {
        $('#bidLevel2').show();
    }

    $('#bidRegion').change(function (e) {
        var options = '';
        $.each(areaData[this.value]['childs'], function (key, val) {
            options += '<option value="' + val.bid_id + '">' + val.name + '</option>';
        })

        $('#bidLevel2').html(options);
        if (areaData[this.value].hide) {
            $('#bidLevel2').hide();
        } else {
            $('#bidLevel2').show();
        }
    })
}

//清空筛选条件
function clearSearchCondition() {
    var $form = $("#smartFilterForm");
    $form.find("input[type='checkbox']").each(function () {
        $(this).attr("checked", false);
    });
    $form.find("input[type='text']").each(function () {
        $(this).val("");
    });
}

var calculating = 0;
var getCalculat = function () {
    var f = function () {
        if (calculating) {
            sem.getListData({url: '/refbid/feed', container: 'autobid_list', tpl: 'smartRefListTpl', callback: function () {
                calculating = this.data.calculating;
                getCalculat();
            }});
        }
    };
    setTimeout(f, 10000)
};


function deleteKeywords(){
    S.handleFun({url: '/autobid/del', container: 'autobid_list', name: 'keyword_ids',
        title: '删除竞价词',
        hint: '请选择竞价词',
        text_a: '您确定删除所选的竞价词吗？',
        text_b: '删除操作不可恢复。',
        callback: function () {
            sem.getListData({url: '/autobid/list', container: 'autobid_list', tpl: 'smartBidListTpl'});
        }
    });
}

var bid = {
	//针对老用户超过100词的处理
	more100Handle:function(){
		$('#modal').html(juicer($('#more100Tpl').html(),{num:sem.hzuserInfoData.keyword_limit}))
		$('#modal').modal();
		$('#more100_btn').click(function(){
			sem.getuserlevel(1,function(data){
				$(document.body).append(juicer($('#keepKeywordsTpl').html(),data[0]));
				$('#keepKeywords').modal();
				sem.keepLevelHandle();
				var $tips = $('#keepKeywords').find('.err_tips');
				$('#re_check').click(function(e){
					e.preventDefault();
					$.ajax({
						type:'get',
						url:'/autobid/del/clear',
						data:'user_id='+sem.userId,
						dataType:'json',
						success:function(data){
							if(data.status == 'success'){
								location.reload();
							}else{
								var code = data.error_code;
								if (code < 7 || (code > 100 && code < 199)) {
									me.errTip(code);
									return;
								}

								if(code == 13){
									$tips.html('删除竞价词失败').show();
								}
							}
						}
					})
					/*
					$('#keepKeywords').modal('hide');
					$('#keepKeywords').remove();
					sem.clearAddKeyword();
					sem.bidKeywordCount = 0;
					sem.addKeywordHandle();
					*/
					//$('#add_keyword_btn').click();
					//
				})
			})
		})
	},
	tagMenu:function(){
		$('#tagMenu').click(function(e){
			var elem = e.target;
			if(elem.nodeName =='LI'){
				sem.tag_id = '';
				$(this).find('li').removeClass('on');
				$(elem).addClass('on');
				sem.pageInitData(sem.page);
			}
			if(elem.nodeName != 'A') return;
			var name = elem.getAttribute('name');
			sem.tag_id = name;
			if(sem.tag_id){
				$('.outtag').show();
			}else{
				$('.outtag').hide();
			}
			$(this).find('li').removeClass('on');
			$(elem).parent().addClass('on');
			sem.pageInitData(sem.page);
		});
	},
	tagHandle:function(){
		//分组事件
		$('.tag').click(function (e) {
			var elem = e.target, type = elem.getAttribute('type');
			e.preventDefault();
			if (!type) return;
			var keyword_ids = $('#autobid_list input[name="all"]').val().replace(/,$/, ''),
				user_id = S.userId,
				treeTitle = $('.title-shut').attr('type');
			switch (type) {
				case 'newtag':
					var tpl = $('#newTagTpl').html();
					$('#modal').html(juicer(tpl, {user_id: user_id, keyword_ids: keyword_ids}))
					$('#modal').modal();
					var $form = $('#newTagForm'), form = $form[0], $err = $('#err_tag');
					$form.submit(function (e) {

						e.preventDefault();
						var tagVal = form.tag.value;
						if (!tagVal) {
							$err.html('请输入分组名称');
							return;
						}else if(tagVal == '我的竞价词'){
							$err.html('分组名称不能为我的竞价词');
						
						} else if (!RegTool.textReg.test(tagVal)) {
							$err.html('请输入包含汉字或英文或数字的分组');
							return;
						} else if (tagVal.length > 10) {
							$err.html('请输入10个字以内的分组');
							return;
						} else {
							$err.html('');
						}

						$.ajax({
							url: form.action,
							data: $form.serialize(),
							dataType: 'json',
							success: function (data) {
								if (data.status == 'success') {
									var keyword_ids = form.keyword_ids.value;
									S.initTag();
									$('#modal').modal('hide');
									if (keyword_ids) {
										$.ajax({
											url: '/autobid/modify_tag',
											type: 'get',
											data: 'user_id=' + sem.userId + '&keyword_ids=' + form.keyword_ids.value + '&tag_id=' + data.tag_id,
											dataType: 'json',
											success: function (data) {
												if (data.status != 'success') {
													var code = data.error_code;
													if (code < 7 || (code > 100 && code < 199)) {
														sem.errTip(code);
														return;
													}
													return;
												}
												tagRefeshList();
											}
										})
									}
								} else {
									var code = data.error_code;
									if (code == 1) {
										location.href = '/';
									}
									if (code < 7) {
										S.errTip(code);
										return;
									}
									switch (code) {
										case '7':
										case '8':
											$err.html('字段校验失败');
											break;
										case '9':
											$err.html('标签校验失败');
											break;
										case '10':
											$err.html('添加失败');
											break;
										case '11':
											$err.html('分组不能超过100个');
											break;
										default:
											$err.html('系统错误');
									}
								}
							}
						})

					});
					break;
				case 'outtag':
				case 'modifytag':
					if (!S.checkSelected({container: 'autobid_list', name: 'keyword_ids', hint: '请选择竞价词'})) return;
					var tag_id = '2',name = $(elem).text();
					if (type == 'modifytag') {
						tag_id = elem.getAttribute('tag_id');
					}
					$.ajax({
						url: '/autobid/modify_tag',
						data: 'user_id=' + user_id + '&keyword_ids=' + keyword_ids + '&tag_id=' + tag_id,
						dataType: 'json',
						success: function (data) {
							if (data.status == 'success') {
								if (type == 'modifytag') {
									sem.modalAlert('成功移动至 '+name+' 分组');
								}
								tagRefeshList();
							} else {
								var code = data.error_code;
								if (code < 7 || (code > 100 && code < 199)) {
									sem.errTip(code)
									return;
								}
								switch (code) {
									case '7':
									case '8':
									case '9':
										$err.html('字段校验失败');
										break;
									case '10':
										S.modalAlert('关键词不属于用户');
										break;
									case '12':
										S.modalAlert('核心竞价词中的关键词不能超过60');
										break;
									default:
										S.modalAlert("添加标签失败")
								}
							}

						}
					});

					break;
				case 'deltag':
					var tag_id = $(elem).parent().find('a').attr('tag_id'),
						tag = $(elem).parent().text();
					sem.handleFun({url: '/autobid/del_tag', container: 'autobid_list', param: 'tag_id', value: tag_id, name: 'keyword_ids',
						title: '删除标签',
						text_a: '您确定删除' + tag + '标签吗？',
						text_b: '删除标签后，已设该标签的关键词标签将同时被移除。',
						callback: function () {
							location.reload();
						}
					});
					break;

			}
		});
	
	},
	addKeyword:function(){
		$('#add_bid').click(function(){
			if(Number(sem.hzuserInfoData.autobid_keyword_amount) >= Number(sem.hzuserInfoData.keyword_limit)){
				$('#modal').html(juicer($('#checkMore100Tpl').html(),{num:sem.hzuserInfoData.keyword_limit}));
				$('#modal').modal();
				return;
			}
			sem.addKeywordHandle();
		})
	
	},
	setKeyword:function(){
		//开启竞价或设置竞价,打开浮层
		$(".g-change").on("click", ".open-bid", function () {

			var $trigger = $(this),
				type = $trigger.attr("data"),//是批量操作，还是表格内部操作，对于竞价设置为批量操作
				isSet = $trigger.attr("type") || "",
				id = "",
				bidStatus = "",//竞价状态
				crossRegion = [],
				o = {};//构建浮层填充内容所需参数

			//表格内部单个开启，需判断关键词是否开启
			if (type == "single") {
				var $tr = $($trigger.parents("tr")[0]) || "",
					$input = $tr.find("input[name='ids']");

				//$("#g_ids").val($tr.find("input[name='ids']").val());

				id = $input.val();

				crossRegion = sem.getCrossArea({"id": id});
				o = {
					"max_bid": $input.attr('max'),
					"region": crossRegion,
					"strategy": $input.attr('strategy'),
					"rank": $input.attr('rank'),
					"rank1":$input.attr('rank').replace(/^(.)/,function($1){
						if($1 == '1'){
							return '左';
						}else{
							return '右';
						}
					}),
					"keyword_ids": id,
					"snipe": $input.attr('snipe'),
					"domain": $input.attr('snipe_domain'),
					"snipe_without": $input.attr('snipe_without_strategy'),
					"snipe_strategy": $input.attr('snipe_strategy')
				};
				createBidLayer(o);
				return;
			}

			/*
			 *  (1)批量开启，1判断是否有选中，2判断是否全部是未开启，3判断关键词所属计划等状态是否有效
			 *  (2)设置竞价，1判断是否有选中，2判断是否全部都是开启，3判断是选择一个还是多个，
			 *  4如果是1个则填充这一个的数据，如果是多个则出价显示“各异”，其他均为默认
			 */
			//开启竞价
			if (type == "batch") {
				id = $("#autobid_list").find("input[name='all']").val().replace(/,$/, '');
				//check step one
				if (!sem.checkSelected({container: 'autobid_list', hint: '请选择竞价词'})) return false;
				crossRegion = sem.getCrossArea();
			}

			o = {
				"max_bid":"",
				"region":crossRegion,
				"strategy":"",
				"rank":"",
				"keyword_ids":id,
				"snipe":"",
				"snipe_domain":"",
				"snipe_without":"",
				"snipe_strategy":"", 
				"rank1":""
			};

			if ($("input[name='ids']:checked").length == 1) {
				var $current = $("input[name='ids']:checked");
				o.max_bid = $current.attr("max");
				//o.selected = $current.attr("bidarea");
				o.strategy = $current.attr("strategy");
				o.rank = $current.attr("rank");
				o.snipe = $current.attr('snipe');
				o.domain = $current.attr('snipe_domain');
				o.snipe_without = $current.attr('snipe_without_strategy');
				o.snipe_strategy = $current.attr('snipe_strategy');
				o.rank1 = o.rank.replace(/^(.)/,function($1){
					if($1 == '1'){
						return '左';
					}else if($1 == '@'){
						return '右';
					}else{
						return '';
					}
				});

			}
			createBidLayer(o);

		});
		//开启竞价，浮层是新增结构，因此需要绑定事件到body上
		$("body").on("click", "#btnOpenBid", function () {
			var $layer = $("#modalOpenBid"),
				$tip = $layer.find(".err_tips"),
				rateType = $('input:radio[name="ratetype"]:checked').val(),
				url = "/autobid/modify",
				form = $('#open_bidform')[0],
				bidData = {"user_id": hzUserInfoData.default_bind_user, "keyword_ids": form.keyword_ids.value};


			bidData.target_rank = Number(form.rank.value);
			if(bidData.target_rank <= 0 || !bidData.target_rank){
				$tip.html("请选择期望排名").show();
				return;
			}

			bidData.max_bid = $layer.find(".max-bid").val();
			bidData.bid_area = $("#bidLevel2").val();
			bidData.strategy = $("#bidStrategy").val();
			if (form.snipe.value == 1) {
				bidData.snipe = form.snipe.value;
				bidData.snipe_strategy = form.snipe_strategy.value;
				bidData.snipe_without_strategy = '';
				bidData.snipe_domain = form.snipe_domain.value;
				if (bidData.snipe_domain == '') {
					$tip.html("请输入竞争对手主域名").show();
					return;
				} else {
					if (!/^http:\/\//.test(bidData.snipe_domain)) {
						bidData.snipe_domain = 'http://' + bidData.snipe_domain;
					}
					if (!RegTool.urlReg.test(bidData.snipe_domain)) {
						$tip.html("竞争对手主域名格式错误").show();
						return;
					}
				}
				$(form).find('input[name="snipe_without_strategy"]').each(function () {
					if (this.checked) {
						bidData.snipe_without_strategy = this.value;
					}
				});

			} else {
				bidData.snipe = 0;
			}

			if (bidData.max_bid == "" || bidData.max_bid < 0.01 || bidData.max_bid > 999.99 || !RegTool.ftTwoDecimal.test(bidData.max_bid)) {
				$tip.html("最高出价必须在0.01到999.99之间").show();
				$layer.find(".max-bid").focus();
				return false;
			}

			$tip.html('');

			$.ajax({
				type:'get',
				url:url,
				dataType:'json',
				data:bidData,
				success:function (data) {
					if (data.status == "success") {
						$("#modalOpenBid").modal("hide");
						$('#modalOpenBid').remove();
						getFilterList();
					} else {
						var errorCode = data.error_code, errorMsg = "系统错误";
						if (errorCode < 7 || (errorCode > 100 && errorCode < 199)) {
							sem.errTip(errorCode)
							return;
						}
						$tip.html("开启竞价失败：" + errorMsg).show();
					}
				}
			});
		});
	},
	switchKeyword:function(){
		//单项操作启用停止竞价
		$("#autobid_list").on("click", ".bid-operate", function () {
			var $me = $(this),
				$operateBtn = $me.parent(),
				$tr = $($me.parents("tr")[0]),
				keywordId = $tr.find("input[name='ids']").val(),
				bidStatus = $operateBtn.hasClass("bid-pause") == true ? 2 : 3,
				creativeStatus = $tr.find(".creative-hide").val(),
				currentTab = $("#nav_tabs").find(".active").index(),
				region = $tr.find('input[name="ids"]').attr('region'),
				bid_region = Area.bidAreaToRegion($tr.find('input[name="ids"]').attr('bidarea'));

			if (!region) {
				region = sem.userPromote_area;
			}

			/*
			if (!Area.checkInregion(region, bid_region)) {
				sem.modalAlert("关键词的智能竞价地域不属于所属计划推广地域");
				return;
			}
			*/

			$.ajax({
				type: 'get',
				url: '/autobid/modify_bid_status',
				dataType: 'json',
				cache: false,
				data: {"user_id": hzUserInfoData.default_bind_user, "keyword_ids": keywordId, "bid_status": bidStatus},
				success: function (data) {
					if (data.status == "success") {
						if (bidStatus == 2) {
							$operateBtn.removeClass('bid-pause').addClass('bid-open').addClass('bid-waiting');
							$operateBtn.find('.bid-icon-text').text('等待竞价');
							$operateBtn.parents('tr').find('input[name="ids"]').attr('bid_status', 2);
							$tr.find('.pause_reason').hide();
							if(!new RegExp(keywordId,'g').test(sem.refreshKeywords)){
								if(sem.refreshKeywords){
									sem.refreshKeywords += ','+keywordId;
								}else{
									sem.refreshKeywords = keywordId;
								}
							}
						}else{
							$operateBtn.removeClass('bid-open').addClass('bid-pause');
							$operateBtn.find('.bid-icon-text').text('竞价暂停');
							$operateBtn.parent().find('.tagtips-box').hide();
							$operateBtn.parents('tr').find('input[name="ids"]').attr('bid_status', 3);
							if(new RegExp(keywordId,'g').test(sem.refreshKeywords)){
								sem.refreshKeywords = sem.refreshKeywords.replace(new RegExp(keywordId+',?'),'');
							}
							$tr.find('.rank').html('--');
							$tr.find('.feedback-td .refresh').html('');
						}

					} else {
						var errorCode = data.error_code, errorMsg = "系统错误";
						if (errorCode < 7 || (errorCode > 100 && errorCode < 199)) {
							sem.errTip(errorCode)
							return;
						}
						switch (errorCode) {
							case "14":
								errorMsg = "关键词的智能竞价地域不属于所属计划推广地域";
								break;
						}
						sem.modalAlert(errorMsg);
					}
				}
			});
		});
		//批量操作开启暂停关键词
		$("#btnSmartOn").on("click", function () {
			var isClose = true, region = '', bid_region = '', isregion = true;
			if (!sem.checkSelected({container: 'autobid_list', hint: '请选择竞价词'})) return false;
			$("#autobid_list").find("input[name='ids']:checked").each(function () {
				if (this.getAttribute('bid_status') == 1) {
					isClose = false;
					return;
				} else {
					region = this.getAttribute('region');
					bid_region = Area.bidAreaToRegion(this.getAttribute('bidarea'));
					if (!region) {
						region = sem.userPromote_area;
					}
					if (!Area.checkInregion(region, bid_region)) {
						isregion = false;
						return true;
					}
				}
			});

			/*
			if (!isClose) {
				sem.modalAlert("所选关键词尚未进行竞价设置，请设置后再启用。");
				return false;
			}
			if (!isregion) {
				sem.modalAlert("关键词的智能竞价地域不属于所属计划推广地域");
				return;
			}
			*/

			sem.handleFun({url: '/autobid/modify_bid_status', container: 'autobid_list', name: 'keyword_ids',
				title: '开启推广关键词竞价',
				text_a: '您确定要开启所选的推广关键词竞价吗？',
				text_b: '',
				param: 'bid_status',
				value: 2,
				callback: function () {
					getFilterList()
				}
			});
		});
		//批量暂停竞价词
		$("#btnSmartOff").on("click", function () {
			var isClose = true;
			if (!sem.checkSelected({container: 'autobid_list', hint: '请选择竞价词'})) return false;
			$("#autobid_list").find("input[name='ids']:checked").each(function () {
				if ($($(this).parents("tr")[0]).find(".bid-status").text() == "未开启") {
					isClose = false;
					return;
				}
			});

			if (!isClose) {
				sem.modalAlert("未开启竞价的关键词不能执行暂停操作");
				return false;
			}

			sem.handleFun({url: '/autobid/modify_bid_status', container: 'autobid_list', name: 'keyword_ids',
				title: '暂停推广关键词竞价',
				text_a: '您确定要暂停所选的推广关键词竞价吗？',
				text_b: '',
				param: 'bid_status',
				value: 3,
				callback: function () {
					getFilterList()
				}
			});
		});
	
	},
	competior:function(){
    //对手跟踪
    $('#portlet_tab3').click(function (e) {
        var elem = e.target, type = elem.getAttribute('type-set'), st = elem.getAttribute('status');
        e.preventDefault();

        if (elem.nodeName != 'A') return;
        if (type) {
            if (!sem.checkSelected({container: 'autobid_list', hint: '请选择竞价词'})) return false;
            var keyword_ids = '';
            var info = {};
            var tpl = $('#competitorOpenTpl').html();
            info.user_id = sem.userId;
            if (type == 'open') {
                keyword_ids = sem.getCompetitor({status: 'off'}).join(',');
                if (!keyword_ids) {
                    sem.modalAlert("已开启的关键词不能重复开启");
                    return;
                }
                info.keywords = keyword_ids;
                //开启跟踪获取全部区域
                info.area = Area.intersect(['9999999']);
                info.tips = '';
            } else {
                var arr = sem.getCompetitor({status: 'on'});
                keyword_ids = arr.join(',');
                if (!keyword_ids) {
                    sem.modalAlert("未开启的关键词不能使用跟踪设置");
                    return;
                }

                //取对手交集
                var competitors = [], c = [], a = [], domains = [], areas = [], area = '';
                $.each(arr, function (index) {
                    var input = $('#autobid_list').find('input[value="' + arr[index] + '"]'), i = input.attr('competitors'),
                        a = input.attr('competitors_area');
                    competitors.push(i.split(','));
                    areas.push(a);
                });

                c = competitors[0];
                area = areas[0];
                if (competitors.length > 1) {
                    for (var j = 0, jl = competitors.length; j < jl; j++) {
                        var competitor = competitors[j];
                        domains = [];
                        for (var b = 0, bl = c.length; b < bl; b++) {
                            for (var i = 0, il = competitor.length; i < il; i++) {
                                if (c[b].replace(/^www\./, '') == competitor[i].replace(/^www\./, '')) {
                                    domains.push(c[b])
                                }
                            }
                        }
                        c = domains;
                    }
                    info.tips = '您有' + (3 - domains.length) + '个竞争对手设置各异，保存后将覆盖原设置'
                } else {
                    domains = c;
                    info.tips = '';
                }
                $.each(areas, function (index) {
                    if (index > 0) {
                        if (areas[index] !== area) {
                            area = '';
                        }
                    }

                });
                info.area = Area.intersect(['9999999'], area);
                info.domains = domains;
                info.domain = domains.length > 0 ? domains.join(',') + ',' : '';
                info.keywords = keyword_ids;
            }
            $('#modal').html(juicer(tpl, info));
            var select1 = $('#competitor_area'), select2 = $('#competitor_area2'), op = '',
                selected = select1.val();
            $.each(info.area[selected]['childs'], function (key, val) {
                if (val.default) {
                    op += '<option value="' + val.bid_id + '" selected>' + val.name + '</option>';
                } else {
                    op += '<option value="' + val.bid_id + '">' + val.name + '</option>';
                }
            });
            select2.html(op);
            if (info.area[selected].hide) {
                $('#calcLevel2').hide();
            } else {
                $('#calcLevel2').show();
            }

            select1.change(function (e) {
                var options = '';
                $.each(info.area[this.value]['childs'], function (key, val) {
                    options += '<option value="' + val.bid_id + '">' + val.name + '</option>';
                })

                select2.html(options);
                if (info.area[this.value].hide) {
                    select2.hide();
                } else {
                    select2.show();
                }
            });
            $('#modal').modal();
            var $form = $('#competitorForm'), form = $form[0], $err = $('#err_competitor'),
                $list = $('#competitor_list'), domains = form.domains;

            $('#competitorCheck').click(function () {
                var domain = form.url.value;
                if (domain == '') {
                    $err.html('请输入竞争对手');
                    return false
                }
                this.href = 'http://www.baidu.com/#wd=' + domain;
            });

            $('#add_competitor').click(function () {
                var url = form.url.value, d = domains.value.split(',');
                if (d.length > 3) {
                    $err.html('最多只能添加3个竞争对手');
                    return;
                }

                if (url == '') {
                    $err.html('请输入竞争对手url');
                    return;
                } else {
                    url = /^http:\/\//.test(url) ? url : 'http://' + url;
                    if (!RegTool.urlReg.test(url)) {
                        $err.html('竞争对手域名格式错误');
                        return;
                    }
                    url = url.replace('http://', '');
                    var reg = new RegExp(url, 'g');
                    if (reg.test(domains.value)) {
                        $err.html('输入竞争对手重复');
                        return;
                    }
                }
                form.url.value = '';
                $err.html('');
                url = url.replace('http://', '');

                $list.append('<li><i class="hzicon white-list-icon"></i>' + url + '<span class="pull-right cursor" name="' + url + '">删除</span></li>');

                domains.value += url + ',';
            });


            $list.click(function (e) {
                var elem = e.target;
                if (elem.nodeName != 'SPAN') return;
                var name = elem.getAttribute('name'), val = domains.value;
                domains.value = val.replace(name + ',', '');
                $(elem).parent().remove();

            });

            $form.submit(function (e) {
                e.preventDefault();
                if (domains.value == '') {
                    $err.html('请添加竞争对手');
                    return;
                }
                if (form.domains.value.split(',').length > 3) {
                    $err.html('最多可添加3个竞争对手');
                    return;
                }
                $err.html('');
                domains.value = domains.value.replace(/,$/, '');

                $.ajax({
                    url: '/competitor/add',
                    type: 'get',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function (data) {
                        if (data.status == 'success') {
                            S.getListData({url: '/competitor/feed', container: 'autobid_list', page: 1, page_size: 10, tpl: 'competitorListTpl'});
                            $('#modal').modal('hide');
                        } else {
                            var code = data.error_code;
                            if (code < 7 || (code > 100 && code < 199)) {
                                sem.errTip(code)
                                return;
                            }
                            var $errTips = $('#compeittor_tips');
                            switch (code) {
                                case '7':
                                    $errTips.html('账户id校验失败');
                                    break;
                                case '8':
                                    $errTips.html('关键词id校验失败');
                                    break;
                                case '9':
                                    $errTips.html('对手url格式校验失败');
                                    break;
                                case '10':
                                    $errTips.html('跟踪地域校验失败');
                                    break;
                                case '11':
                                    $errTips.html('关键词不属于该账户');
                                    break;
                                case '12':
                                    $errTips.html('开启跟踪的关键词超过了90的最大限制');
                                    break;
                                case '13':
                                    $errTips.html('插入失败');
                                    break;
                            }
                        }
                    }
                })

            });

        }
        if (st) {
            if (!sem.checkSelected({container: 'autobid_list', hint: '请选择竞价词'})) return false;
            var isClose = true;
            if (!sem.checkSelected({container: 'autobid_list', hint: '请选择竞价词'})) return false;
            $("#autobid_list").find("input[name='ids']:checked").each(function () {
                if ($(this).parents("tr").find("input[name='ids']").attr('competitor_status') == 1) {
                    isClose = false;
                    return;
                }
            });

            if (!isClose) {
                sem.modalAlert("未开启竞价的关键词不能执行开启操作");
                return false;
            }
            if (st == 'on') {

                var keyword_ids = sem.getCompetitor({status: 'off'}).join(',');
                if (keyword_ids == '') {
                    sem.modalAlert("所选的关键词为未开启或暂停状态");
                    return false;
                }

                sem.handleFun({url: '/competitor/modify', container: 'autobid_list', name: 'keyword_ids',
                    title: '开启关键词对手跟踪',
                    text_a: '您确定要开启所选关键词的对手跟踪吗？',
                    text_b: '',
                    param: 'status',
                    value: 2,
                    callback: function () {
                        S.getListData({url: '/competitor/feed', container: 'autobid_list', page: 1, page_size: 10, tpl: 'competitorListTpl'});
                    }
                });
            } else {
                var keyword_ids = sem.getCompetitor({status: 'on'}).join(',');

                if (keyword_ids == '') {
                    sem.modalAlert("所选的关键词为未开启或暂停状态");
                    return false;
                }

                sem.handleFun({url: '/competitor/modify', container: 'autobid_list', name: 'keyword_ids',
                    title: '开启关键词对手跟踪',
                    text_a: '您确定要开启所选关键词的对手跟踪吗？',
                    text_b: '',
                    param: 'status',
                    value: 3,
                    callback: function () {
                        S.getListData({url: '/competitor/feed', container: 'autobid_list', page: 1, page_size: 10, tpl: 'competitorListTpl'});
                    }
                });

            }

        }


    });
			  
	},
	listOperate:function(){
		//列表中的单项操作
		$('#autobid_list').click(function (e) {
			var elem = e.target;
			if (elem.nodeName !== 'I') return;

			var type = elem.getAttribute('type'),
				id = $(elem).parents('tr').find('input[name="ids"]').val();

			switch (type) {
				case 'photo':
					$('.photo').find('.err_tips').hide();
					var target_rank = elem.getAttribute('target_rank').replace(/^(.)/, function ($1) {
							if ($1 == 1) {
								return '左';
							} else {
								return '右';
							}
						}),
						rank = elem.getAttribute('rank').replace(/^(.)/, function ($1) {
							if ($1 == 1) {
								return '左';
							} else {
								return '右';
							}
						});
						var info = {};
						info.target_rank = target_rank;
						info.rank = rank;
						info.keyword_name = elem.getAttribute('keyword');
					$.ajax({
						type: 'get',
						url: '/autobid/rank_snap',
						data: 'user_id=' + sem.userId + '&keyword_id=' + id,
						dataType: 'json',
						success: function (data) {
							if (data.status == 'success') {
								info.time = data.data.time;
								$('#modal').html(juicer($('#photo_tpl').html(), info));
								$('#modal').modal({width: 600})
								$('#modal').modal();
								var ua = window.navigator.userAgent;
								if(data.data.length == 0){
									data.data.snap = '';
								}
								if (/Chrome/.test(ua)) {
									$('#iframe').contents().find('body').html(data.data.snap + '<div style="position:fixed;left:0;top:0;width:600px;height:300px;"></div>')
								} else {
									$('#iframe').load(function () {
										var body = $(this).contents().find('body');
										body.html(data.data.snap + '<div style="position:fixed;left:0;top:0;width:600px;height:300px;"></div>')
									})
								}
							} else {
								var code = data.error_code;
								if (code < 7 || (code > 100 && code < 199)) {
									sem.errTip(code)
									return;
								}
							}

						}
					});

					break;
				case 'rank_monitor':
					var snipe = elem.getAttribute('snipe'), keyword = elem.getAttribute('keyword');
					$.ajax({
						url: '/autobid/monitor',
						type: 'get',
						data: 'user_id=' + sem.userId + '&keyword_id=' + id,
						dataType: 'json',
						success: function (data) {
							if (data.status !== 'success') {
								var code = data.error_code;
								if (code < 7 || (code > 100 && code < 199)) {
									sem.errTip(code)
									return;
								}
							}
							var m = $('#modalMonitor');
							if (m.length > 0) {
								m.remove();
							}
							$(document.body).append(juicer($('#monitorSetTpl').html(), {
								monitor_rank: data.monitor_rank.replace('1', ''),
								mobile: hzUserInfoData.mobile,
								rank: [1, 2, 3, 4, 5, 6, 7, 8],
								keyword_id: id,
								keyword: keyword,
								is_agent: sem.is_agent,
								access: sem.access_list
							}));

							$('#modalMonitor').modal();
							var obj = {arrX: [], rank: [], bid: [], compete_rank: []}, chardata = data.data;
							for (i in chardata) {
								obj.arrX.push(chardata[i]['moni_time']);
								obj.bid.push(Number(chardata[i]['bid']));
								if (chardata[i].rank < 20 && chardata[i].rank > 0) {
									obj.rank.push(Number(chardata[i]['rank'].replace('1', '')));
								} else if (chardata[i].rank == -3) {
									obj.rank.push(null);
								} else {
									obj.rank.push(9);
								}

								if (chardata[i].compete_rank < 20 && chardata[i].compete_rank > 0) {
									obj.compete_rank.push(Number(chardata[i]['compete_rank'].replace('1', '')));
								} else {
									obj.compete_rank.push(9);
								}
							}

							//获取非推广时段
							var plotBandsArr = {}, plotBands = {}, a = 0, plotBand = [];
							for (var j in chardata) {
								if (chardata[j]['rank'] == -2) {
									plotBands[a] += j + ',';
								} else {
									if (plotBands[a]) {
										a++;
									}
								}
							}
							for (var b in plotBands) {
								plotBandsArr[b] = plotBands[b].replace(/undefined|,$/g, '').split(',');
							}
							for (var c in plotBandsArr) {
								plotBand.push({
									color: '#ffebd7',
									from: Number(plotBandsArr[c][0]),
									to: Number(plotBandsArr[c][plotBandsArr[c].length - 1])
								})
							}

							var seriesData = (snipe == 1) ? [
								{name: '出价', yAxis: 1, type: 'column', color: '#ade6cc', data: obj.bid, marker: {symbol: 'circle'}},
								{name: '排名', yAxis: 0, type: 'line', color: '#28be7a', data: obj.rank, marker: {symbol: 'circle'}},
								{name: '竞争对手排名', yAxis: 0, type: 'line', color: '#f5a145', data: obj.compete_rank, marker: {symbol: 'circle'}},
								{name: '非推广时段或未竞价', type: 'column', color: '#ffebd7'}
							] : [
								{name: '出价', yAxis: 1, type: 'column', color: '#ade6cc', data: obj.bid, marker: {symbol: 'circle'}},
								{name: '排名', yAxis: 0, type: 'line', color: '#28be7a', data: obj.rank, marker: {symbol: 'circle'}},
								{name: '非推广时段或未竞价', type: 'column', color: '#ffebd7'}
							];

							//初始化统计图表数据
							var objChart = {
								xcategory: {tickInterval: 5, "categories": obj.arrX,
									plotBands: plotBand,
									"labels": {
										formatter: function () {
											return this.value.substr(5, 11);
										},
										style: {color: '#999'}
									}, lineColor: '#dfe5e3'
								},
								data: seriesData,
								yAxisArr: [
									{title: {text: '关键词左侧推广排名', style: {color: '#999'}}, zIndex: 10, tickPositions: [9, 8, 7, 6, 5, 4, 3, 2, 1], "labels": {style: {color: '#999'}}, gridLineColor: '#dfe5e3', offset: 10},
									{title: {text: '关键词出价（元）', style: {color: '#999'}}, "labels": {style: {color: '#999'}}, opposite: true, gridLineColor: '#dfe5e3'}
								]
							};

							$("#monitor_chart").highcharts({
								chart: {width: 850, height: 250},
								title: {text: '', style: {color: '#999'}},
								legend: {
									verticalAlign: "top",
									align: "center",
									borderWidth: 0,
									itemStyle: {
										color: '#666'
									}
								},
								plotOptions: {
									series: {
										connectNulls: true
									}
								},
								credits: {enabled: false},
								xAxis: objChart.xcategory,
								yAxis: objChart.yAxisArr,
								tooltip: {
									formatter: function () {
										if (this.series.name == '出价') {
											return  '<b style="color:#666">' + this.series.name + ':<span style="color:#ade6cc">' + this.y + '</span>元</b><br/><span style="color:#999;font-size:11px;">' + this.x + '</span>';
										}
										if (this.series.name == '排名') {
											return  '<b style="color:#666;">' + this.series.name + ':<span style="color:#28be7a">' + this.y + '</span></b><br/><span style="color:#999;font-size:11px;">' + this.x + '</span>';
										}
										if (this.series.name == '竞争对手排名') {
											return  '<b style="color:#666;">' + this.series.name + ':<span style="color:#f5a145">' + this.y + '</span></b><br/><span style="color:#999;font-size:11px;">' + this.x + '</span>';
										}
									}
								},
								series: objChart.data
							});

							//发送短信
							var form = $('#msgForm')[0];

							$('#checkMsg').change(function (e) {
								if (this.checked) {
									var rank = form.rank.value;
									if (!rank) return;

									$.ajax({
										url: '/autobid/monitor_set',
										type: 'get',
										data: 'keyword_id=' + form.keyword_id.value + '&user_id=' + sem.userId + '&rank=' + form.rank.value,
										dataType: 'json',
										success: function (data) {
											if (data.status != 'success') {
												var code = data.error_code;
												if (code < 7 || (code > 100 && code < 199)) {
													sem.errTip(code);
													return;
												}
											}

										}
									})
								} else {
									$.ajax({
										url: '/autobid/monitor_set',
										type: 'get',
										data: 'keyword_id=' + form.keyword_id.value + '&user_id=' + sem.userId + '&rank=',
										dataType: 'json',
										success: function (data) {
											if (data.status != 'success') {
												var code = data.error_code;
												if (code < 7 || (code > 100 && code < 199)) {
													sem.errTip(code);
													return;
												}
											}
										}
									})

								}
							});
							$('#rankMsg').change(function () {
								var val = this.value;

								if (!form.check.checked || !this.value) return;
								//	if(initVal == rank) return;
								$.ajax({
									url: '/autobid/monitor_set',
									type: 'get',
									data: 'keyword_id=' + form.keyword_id.value + '&user_id=' + sem.userId + '&rank=' + val,
									dataType: 'json',
									success: function (data) {
										if (data.status != 'success') {
											var code = data.error_code;
											if (code < 7 || (code > 100 && code < 199)) {
												sem.errTip(code)
												return;
											}
										}
									}
								});

							});

						}
					});
					break;
				case 'competitor_rank':
					var keyword = elem.getAttribute('keyword'),
						competitors = $(elem).parents('tr').find('input[name="ids"]').attr('competitors'),
						competitor_id = elem.getAttribute('id');

					$.ajax({
						url: '/competitor/rank',
						type: 'get',
						data: 'user_id=' + sem.userId + '&keyword_id=' + id,
						dataType: 'json',
						success: function (data) {
							if (data.status !== 'success') {
								var code = data.error_code;
								if (code < 7 || (code > 100 && code < 199)) {
									sem.errTip(code)
									return;
								}
							}
							var m = $('#competitor_rank');
							if (m.length > 0) {
								m.remove();
							}
							$(document.body).append(juicer($('#competitor_rank_tpl').html(), {
								keyword: keyword
							}));

							$('#competitor_rank').modal();
							var info = data.data, obj = {arrX: [], me: [], competitors: []}, chardata = data.data;
							//处理x轴数据
							$.each(info.self_rank, function (index) {
								obj.arrX.push(info.self_rank[index].time);
								if (info.self_rank[index].rank < 0 || info.self_rank[index].rank > 19) {
									obj.me.push(9);
								} else {
									obj.me.push(Number(info.self_rank[index].rank.replace(/^1/, '')));
								}
							});
							var index = 0;
							$.each(info.competitor_rank, function (key, val) {
								obj['competitors'].push({id: key, name: val[0].name, data: []});

								$.each(val, function (k, v) {
									if (v.rank < 0 || v.rank > 19) {
										obj['competitors'][index]['data'].push(9);
									} else {
										obj['competitors'][index]['data'].push(Number(v.rank.replace(/^1/, '')));
									}
								});
								index++;
							});
							var seriesData = [
								{name: '我的排名', type: 'line', color: '#29be7b', data: obj.me, marker: {symbol: 'circle'}}
							], colors = ['#ffb46c', '#4ea7e6', '#a788c7'];
							$.each(obj.competitors, function (index) {
								var val = obj.competitors;
								if (val[index].id == competitor_id) {
									seriesData.push({name: val[index].name, type: 'line', color: colors[index], marker: {symbol: 'circle'}, data: val[index].data});

								} else {
									seriesData.push({name: val[index].name, type: 'line', color: colors[index], visible: false, marker: {symbol: 'circle'}, data: val[index].data});
								}
							});
							$("#competitor_chart").highcharts({
								chart: {width: 800, height: 250},
								title: {text: '', style: {color: '#999'}},
								legend: {
									verticalAlign: "top",
									align: "center",
									borderWidth: 0,
									itemStyle: {
										color: '#666'
									}
								},
								credits: {enabled: false},
								xAxis: {
									tickInterval: 5,
									categories: obj.arrX,
									labels: {
										formatter: function () {
											return this.value.substr(5, 11);
										}
									}
								},
								yAxis: {title: '', tickPositions: [9, 8, 7, 6, 5, 4, 3, 2, 1], "labels": {style: {color: '#999'}}, gridLineColor: '#dfe5e3'},
								tooltip: {
									formatter: function () {
										return  '<b style="color:#666;">' + this.series.name + ':<span style="color:#f5a145">' + this.y + '</span></b><br/><span style="color:#999;font-size:11px;">' + this.x + '</span>';
									}
								},
								series: seriesData
							});

						}
					});

					break;
				//跟踪状态单项操作
				case 'competitor-switch':
					var status = elem.getAttribute('status'), keyword_id = $(elem).parents('tr').find('input[name="ids"]').val();

					$.ajax({
						type: 'get',
						url: '/competitor/modify',
						data: {user_id: sem.userId, keyword_ids: keyword_id, status: status},
						cache: false,
						dataType: 'json',
						success: function (data) {
							if (data.status == 'success') {
								var $me = $(elem).parents('.competitor-operate');
								if (status == 2) {
									elem.setAttribute('status', 3);
									$me.removeClass('bid-pause').addClass('bid-play');
									$me.find('.bid-status').text('跟踪暂停');
									$me.parents('tr').find('input[name="ids"]').attr('competitor_status', 3);
								} else {
									elem.setAttribute('status', 2);
									$me.removeClass('bid-play').addClass('bid-pause');
									$me.find('.bid-status').text('已开启')
									$me.parents('tr').find('input[name="ids"]').attr('competitor_status', 2);
								}
							} else {
								var code = data.error_code;
								if (code < 7 || (code > 100 && code < 199)) {
									sem.errTip(code)
									return;
								}
							}
						}
					});
					break;
			}
		});
	
	}

}

$(function () {
	if(sem.hzuserInfoData.init_flag != 2) return;
	
    sem.initTag();
	sem.pageInitData();
    sem.initWhitehandle();
    sem.initWhitelist('init');
    $('#whiteList_set').find('.bindUsername').text(sem.name);
	if(Number(sem.hzuserInfoData.autobid_keyword_amount) > Number(sem.hzuserInfoData.keyword_limit)){
		bid.more100Handle();
		return;	
	}
	setInterval(function(){
		sem.refreshBidList();
	},5000)
	//分组标签切换
	bid.tagMenu();
	bid.tagHandle();

	//添加竞价词
	bid.addKeyword();
	//修改竞价词设置
	bid.setKeyword();
	//竞价词状态修改
	bid.switchKeyword();

	//列表中的单项操作
	bid.listOperate();

	$('#smartFilterForm').submit(function(e){
        e.preventDefault();
        getFilterList();
	})

    $('#whiteList').click(function () {
        $('#addWhitelistForm')[0].domains.value = '';
    });

	/*
    $("#autobid_list").on("mouseenter","tr",function(){
        $(this).addClass("tr-hover").find("td").eq(1).find("i").removeClass("invisible");
    }).on("mouseleave","tr",function(){
        $(this).removeClass("tr-hover").find("td").eq(1).find("i").addClass("invisible");
    });
	*/
});
