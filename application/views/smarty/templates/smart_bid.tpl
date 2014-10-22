{extends file="base.tpl"}
{block content}

<!-- BEGIN PAGE CONTAINER-->
<div class="wrapper-left no-margin" id="page_content">
    <div class="main transition">
		<div class="page_err hide" id="page_err"></div>
		{errTipsLayer}
		<div class="fixed-area">
        <div class="tabbable g-change operate-area"  id="keyword_handle">
			<div class="pull-right list-exist">
                <div class="filter-layer">
                    <form action="" method="" class="form-horizontal pull-left" id="smartFilterForm">
                        <input id="skeyword" name="keyword" type="text" placeholder="关键词搜索" />
						<input type="submit" value="" class="search-btn pull-right" id="search"/>
                    </form>
                </div>
            </div>
			<div class="btn-bar" id="portlet_tab1">
				<a href="javascript:;" id="add_bid" class="pull-left">添加竞价词</a>
				<div class="list-exist">
                    <button type="button" class="btn btn-default" id="btnSmartOff">暂停竞价</button>
                    <button type="button" class="btn btn-default" id="btnSmartOn">启用竞价</button>
                    <a class="btn btn-default open-bid" data="batch" type="bid">修改竞价设置</a>

                    <div class="btn-group tag">
                        <a href="javascript:;" class="btn btn-border dropdown-toggle boxshadow-bottom" data-toggle="dropdown">移动至...<i class="hzicon angle-down"></i></a>
                        <ul class="dropdown-menu">
                            <li class="tag-list">
                            </li>
                            <li><a href="#" type="newtag">新建分组</a></li>
                            <li class="outtag hide"><a href="#" type="outtag">从该分组中移出</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn" onclick="deleteKeywords()">删除竞价词</button>
                    <a href="#whiteList_set" data-toggle="modal" id="whiteList" class="white">设置白名单</a>
				</div>
			</div>
        </div><!--end tabbable-->
        <div class="tab-content g-change list-exist">
			<div class="tab-list">
				<ul id="tagMenu">
				</ul>
			</div>
        </div><!--end tab-content-->
		</div>
		<div id="autobid_list" class="table-list g-change"></div>
        <div id="modalOpenBidLayer"></div>
        {update_account}
    </div><!--end main-->
</div><!--end wrapper-left-->

<!-- END PAGE -->
</div>
{modal_accounterror}

{/block}
{block customjs append}
{smart_template}
<script type="text/javascript" src="/static/scripts/smart.js?v={$version}"></script>
<script>
{literal}
$(function(){
//	sem.pageInitData('smart');
	sem.page = 'smart';
})
{/literal}
{if $is_tip == 1}
$("body").append('<div class="first-bg"><a href="javascript:;" onclick="$(this).parent().remove();" class="close-first-bg">关闭</a></div>');
{/if}
{if $is_tip == 2}
		$(document.body).append($('#bid_24_tpl').html());
		$('#bid_24_modal').modal();
		$('#set_24').click(function () {
			$.ajax({
				type: 'get',
				url: '/autobid/uninterruptible_set',
				data: 'uninterruptible=1',
				dataType: 'json',
				success: function (data) {
					if (data.status == 'success') {
						$('#bid_24_modal').modal('hide');
					} else {
						var errorCode = data.error_code, errorMsg = "系统错误";
						if (errorCode < 7 || (errorCode > 100 && errorCode < 199)) {
							sem.errTip(errorCode)
							return;
						}
						$('#tips_24').html("开启24小时竞价失败：" + errorMsg).show();
					}

				}
			})

		})
{/if}
{if $show_questionnaire == 1}
{literal}
	$(document.body).append($('#questionnaireTpl').html());
	$('#questionnaire_modal').modal();
	$('#question_y').click(function(){
		$.ajax({
			url:'/questionnaire/put',
			type:'get',
			data:{user_id:sem.userId,res:1},
			dataType:'json',
			success:function(){
				$('#questionnaire_modal .modal-body').html($('#questionokTpl').html());
				$('#questionnaire_modal .y').hide();
				$('#questionnaire_modal').find('.n').show();
			}
		})
	})
	$('#question_n').click(function(){
		$.ajax({
			url:'/questionnaire/put',
			type:'get',
			data:{user_id:sem.userId,res:2},
			dataType:'json',
			success:function(){
				$('#questionnaire_modal').modal('hide');
			}
		})

	})

{/literal}
{/if}
</script>
{/block}
